<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use ZipArchive;

class BackupService
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function createFullBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFileName = "full_backup_{$timestamp}.zip";

        $backupFilePath = storage_path('app/backups/' . $backupFileName);


        if (!File::exists(dirname($backupFilePath))) {
            File::makeDirectory(dirname($backupFilePath), 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($backupFilePath, ZipArchive::CREATE) === TRUE) {
            $this->addFilesToZip($zip, base_path(), [
                'app/',
                'config/',
                'database/',
                'routes/',
                'resources/views/',
                'public/storage/',
                '.env',
                'composer.json',
                'composer.lock',
            ]);

            $this->addDatabaseDumpToZip($zip, $timestamp);

            $result = $zip->close();
            if (!$result) {
                throw new \Exception('Failed to close ZIP file');
            }

        } else {
            throw new \Exception('Không thể tạo file zip backup');
        }

        return $backupFileName;
    }

    private function addFilesToZip(ZipArchive $zip, string $basePath, array $include): void
    {
        foreach ($include as $p) {
            $abs = realpath($basePath . DIRECTORY_SEPARATOR . $p);
            if (!$abs) {
                continue;
            }

            if (is_dir($abs)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($abs, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($files as $file) {
                    $filePath = $file->getPathname();
                    $relativePath = ltrim(
                        str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath),
                        '/\\'
                    );
                    $zip->addFile($filePath, $relativePath);
                }
            } elseif (is_file($abs)) {
                $relativePath = ltrim(
                    str_replace($basePath . DIRECTORY_SEPARATOR, '', $abs),
                    '/\\'
                );
                $zip->addFile($abs, $relativePath);
            }
        }
    }

    public function addDatabaseDumpToZip(ZipArchive $zip, $timestamp)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'db_') . '.sql';

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $command = sprintf(
            'C:\xampp\mysql\bin\mysqldump.exe --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            escapeshellarg($tmp)
        );

        exec($command, $output, $returnCode);

        if (File::exists($tmp) && File::size($tmp) > 0) {
            $zip->addFile($tmp, 'database_backup.sql');
        }
    }

    public function createDatabaseBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return $this->createDatabaseDump($timestamp);
    }

    private function createDatabaseDump($timestamp)
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $backupFileName = "db_backup_{$timestamp}.sql";
        $backupFilePath = $this->backupPath . '/' . $backupFileName;

        $command = sprintf(
            'C:\xampp\mysql\bin\mysqldump.exe --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            escapeshellarg($backupFilePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            return $this->createManualDatabaseDump($timestamp);
        }

        return $backupFileName;
    }

    private function createManualDatabaseDump($timestamp)
    {
        $backupFileName = "db_backup_{$timestamp}.sql";
        $backupFilePath = $this->backupPath . '/' . $backupFileName;

        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');

        $dump = "-- Database: {$dbName}\n";
        $dump .= "-- Generated: " . now()->toDateTimeString() . "\n\n";
        $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];

            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $dump .= "-- Table: {$tableName}\n";
            $dump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $dump .= $createTable->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::select("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                $dump .= "-- Data for table: {$tableName}\n";
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array)$row);

                    $dump .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $dump .= "\n";
            }
        }

        $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        File::put($backupFilePath, $dump);

        return $backupFileName;
    }

    public function restoreFullBackup($backupFileName)
    {
        $backupFilePath = $this->backupPath . '/' . $backupFileName;

        if (!File::exists($backupFilePath)) {
            throw new \Exception('File backup không tồn tại');
        }

        $zip = new ZipArchive();

        if ($zip->open($backupFilePath) !== TRUE) {
            throw new \Exception('Không thể mở file backup');
        }

        $tempExtractPath = storage_path('app/temp_restore');

        // Clean temp directory
        if (File::exists($tempExtractPath)) {
            File::deleteDirectory($tempExtractPath);
        }
        File::makeDirectory($tempExtractPath, 0755, true);

        // Extract backup
        $zip->extractTo($tempExtractPath);
        $zip->close();

        // Restore database if exists
        $dbBackupPath = $tempExtractPath . '/database_backup.sql';
        if (File::exists($dbBackupPath)) {
            $this->restoreDatabase($dbBackupPath);
        }

        $filesToRestore = [
            'config',
            'resources',
            'public/storage',
        ];

        $basePath = base_path();

        foreach ($filesToRestore as $item) {
            $sourcePath = $tempExtractPath . '/' . $item;
            $destPath = $basePath . '/' . $item;

            if (File::exists($sourcePath)) {
                if (File::exists($destPath)) {
                    $backupCurrentPath = $destPath . '_backup_' . now()->timestamp;
                    File::move($destPath, $backupCurrentPath);
                }

                if (File::isDirectory($sourcePath)) {
                    File::copyDirectory($sourcePath, $destPath);
                } else {
                    File::copy($sourcePath, $destPath);
                }
            }
        }

        File::deleteDirectory($tempExtractPath);

        return true;
    }

    public function restoreDatabase($sqlFilePath)
    {
        if (!File::exists($sqlFilePath)) {
            throw new \Exception('File database backup không tồn tại');
        }

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // MySQL restore command
        $command = sprintf(
            'C:\xampp\mysql\bin\mysql.exe --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            escapeshellarg($sqlFilePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Lỗi khi restore database: ' . implode("\n", $output));
        }

        return true;
    }

    public function getBackupsList()
    {
        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            $fileSize = $file->getSize();
            $createdTime = $file->getMTime();

            $type = 'unknown';
            if (str_contains($fileName, 'full_backup')) {
                $type = 'full';
            } elseif (str_contains($fileName, 'db_backup')) {
                $type = 'database';
            }

            $backups[] = [
                'name' => $fileName,
                'type' => $type,
                'size' => $fileSize,
                'created' => $createdTime,
                'path' => $file->getRealPath()
            ];
        }

        usort($backups, function ($a, $b) {
            return $b['created'] - $a['created'];
        });

        return collect($backups);
    }

    public function deleteBackup($backupFileName)
    {
        $backupFilePath = $this->backupPath . '/' . $backupFileName;

        if (File::exists($backupFilePath)) {
            return File::delete($backupFilePath);
        }

        return false;
    }

    public function downloadBackup($backupFileName)
    {
        $backupFilePath = $this->backupPath . '/' . $backupFileName;

        if (!File::exists($backupFilePath)) {
            throw new \Exception('File backup không tồn tại');
        }

        return response()->download($backupFilePath);
    }
}
