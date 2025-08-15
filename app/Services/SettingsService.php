<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingsService
{
    protected static string $configPath = '';

    protected static function initConfigPath()
    {
        if (!self::$configPath) {
            self::$configPath = config_path('app_settings.php');
        }
    }

    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = config('app_settings');

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    public static function saveToConfig(array $settings)
    {
        self::initConfigPath();

        // Lấy config hiện tại
        $currentConfig = config('app_settings');

        // Update với settings mới
        $updatedConfig = self::updateNestedArray($currentConfig, $settings);

        // Convert array thành PHP code
        $configContent = "<?php\n\nreturn " . self::arrayToPhp($updatedConfig) . ";\n";

        // Ghi vào file
        File::put(self::$configPath, $configContent);

        // Clear config cache
        Artisan::call('config:clear');

        return true;
    }

    /**
     * Update nested array
     */
    private static function updateNestedArray($array, $updates)
    {
        foreach ($updates as $key => $value) {
            $keys = explode('.', $key);
            $current = &$array;

            foreach ($keys as $k) {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }

            $current = $value;
        }

        return $array;
    }

    /**
     * Convert array to PHP code
     */
    private static function arrayToPhp($array, $indent = 1)
    {
        $php = "[\n";
        $indentStr = str_repeat('    ', $indent);

        foreach ($array as $key => $value) {
            $php .= $indentStr . "'{$key}' => ";

            if (is_array($value)) {
                $php .= self::arrayToPhp($value, $indent + 1);
            } elseif (is_bool($value)) {
                $php .= $value ? 'true' : 'false';
            } elseif (is_string($value)) {
                $php .= "'" . addslashes($value) . "'";
            } else {
                $php .= $value;
            }

            $php .= ",\n";
        }

        $php .= str_repeat('    ', $indent - 1) . "]";
        return $php;
    }


    public static function getAllSettings()
    {
        return config('app_settings');
    }

    public static function getGroup($group)
    {
        return config("app_settings.{$group}", []);
    }

}
