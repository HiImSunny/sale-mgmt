<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SettingsController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $settings = SettingsService::getAllSettings();

        return view('settings.index', compact('settings', ));
    }

    public function update(Request $request)
    {
        try {
            $settings = $this->processFormData($request->all());
            SettingsService::saveToConfig($settings);

            return redirect()->back()->with('success', 'Cài đặt đã được cập nhật thành công!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function processFormData($data)
    {
        $settings = [];

        // Loyalty settings
        if (isset($data['loyalty_enabled'])) {
            $settings['loyalty.enabled'] = $data['loyalty_enabled'] === '1';
        }

        // Rank thresholds
        $rankKeys = [
            'rank_bronze_min' => 'loyalty.ranks.bronze_min_amount',
            'rank_silver_min' => 'loyalty.ranks.silver_min_amount',
            'rank_gold_min' => 'loyalty.ranks.gold_min_amount',
            'rank_platinum_min' => 'loyalty.ranks.platinum_min_amount',
        ];

        foreach ($rankKeys as $formKey => $configKey) {
            if (isset($data[$formKey])) {
                $settings[$configKey] = (int)$data[$formKey];
            }
        }

        // Discount settings
        $discountRanks = ['regular', 'bronze', 'silver', 'gold', 'platinum'];

        foreach ($discountRanks as $rank) {
            if (isset($data["discount_{$rank}_type"])) {
                $settings["loyalty.discounts.{$rank}.type"] = $data["discount_{$rank}_type"];
            }
            if (isset($data["discount_{$rank}_value"])) {
                $settings["loyalty.discounts.{$rank}.value"] = (float)$data["discount_{$rank}_value"];
            }
        }

        // Reward settings
        $rewardKeys = [
            'points_rate' => 'loyalty.rewards.points_rate',
            'points_value' => 'loyalty.rewards.points_value',
        ];

        foreach ($rewardKeys as $formKey => $configKey) {
            if (isset($data[$formKey])) {
                $settings[$configKey] = (float)$data[$formKey];
            }
        }

        // General settings
        $generalKeys = [
            'site_name' => 'general.site_name',
            'contact_email' => 'general.contact_email',
            'contact_phone' => 'general.contact_phone',
        ];

        foreach ($generalKeys as $formKey => $configKey) {
            if (isset($data[$formKey])) {
                $settings[$configKey] = $data[$formKey];
            }
        }

        return $settings;
    }

    public function backupIndex()
    {
        $perPage = 10;
        $page = request()->get('page', 1);

        $allBackups = $this->backupService->getBackupsList();

        $backups = new LengthAwarePaginator(
            $allBackups->forPage($page, $perPage),
            $allBackups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('backup.index', compact('backups'));
    }

    /**
     * Tạo backup full
     */
    public function createFullBackup()
    {
        try {
            $backupFileName = $this->backupService->createFullBackup();
            return redirect()->back()->with('success', "Backup full đã được tạo thành công: {$backupFileName}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi tạo backup: ' . $e->getMessage());
        }
    }

    /**
     * Tạo backup database
     */
    public function createDatabaseBackup()
    {
        try {
            $backupFileName = $this->backupService->createDatabaseBackup();
            return redirect()->back()->with('success', "Backup database đã được tạo thành công: {$backupFileName}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi tạo backup database: ' . $e->getMessage());
        }
    }

    /**
     * Restore từ backup
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        try {
            $backupFileName = $request->backup_file;

            if (str_contains($backupFileName, 'full_backup')) {
                $this->backupService->restoreFullBackup($backupFileName);
                $message = 'Backup full đã được khôi phục thành công';
            } elseif (str_contains($backupFileName, 'db_backup')) {
                $backupFilePath = storage_path('app/backups/' . $backupFileName);
                $this->backupService->restoreDatabase($backupFilePath);
                $message = 'Database đã được khôi phục thành công';
            } else {
                throw new \Exception('Loại backup không được hỗ trợ');
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khôi phục: ' . $e->getMessage());
        }
    }

    /**
     * Download backup
     */
    public function downloadBackup($filename)
    {
        try {
            return $this->backupService->downloadBackup($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi tải backup: ' . $e->getMessage());
        }
    }

    /**
     * Xóa backup
     */
    public function deleteBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        try {
            $deleted = $this->backupService->deleteBackup($request->backup_file);

            if ($deleted) {
                return redirect()->back()->with('success', 'Backup đã được xóa thành công');
            } else {
                return redirect()->back()->with('error', 'Không thể xóa backup');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi xóa backup: ' . $e->getMessage());
        }
    }
}
