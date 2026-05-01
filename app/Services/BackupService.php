<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\DB;

class BackupService
{
    /**
     * Directory where backup files are stored (storage/app/backups/).
     */
    public function backupDir(): string
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Generate a full SQL dump and return the file path.
     * Uses PHP's PDO to export all tables without requiring mysqldump binary.
     */
    public function generate(string $initiatedBy = 'System', string $type = 'manual'): string
    {
        $config = config('database.connections.'.config('database.default'));
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $dbName = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $filename = 'backup_'.date('Ymd_His').'_'.$dbName.'.sql';
        $filepath = $this->backupDir().DIRECTORY_SEPARATOR.$filename;

        $sql = $this->buildDump($host, $port, $dbName, $username, $password);

        file_put_contents($filepath, $sql);

        $size = filesize($filepath) ?: 0;

        BackupLog::create([
            'filename' => $filename,
            'size_bytes' => $size,
            'initiated_by' => $initiatedBy,
            'type' => $type,
            'status' => 'success',
        ]);

        return $filepath;
    }

    /**
     * Build the full SQL dump using PDO (no external binary needed).
     */
    private function buildDump(string $host, int|string $port, string $dbName, string $username, string $password): string
    {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        $pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $output = [];
        $output[] = '-- ICAS Portal Database Backup';
        $output[] = '-- Generated: '.now()->toDateTimeString();
        $output[] = "-- Database: {$dbName}";
        $output[] = "-- --------------------------------------------------------\n";
        $output[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $output[] = "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';";
        $output[] = "SET NAMES utf8mb4;\n";

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Table structure
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $output[] = "\n-- Table structure for `{$table}`";
            $output[] = "DROP TABLE IF EXISTS `{$table}`;";
            $output[] = $createStmt['Create Table'].";\n";

            // Table data
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (! empty($rows)) {
                $output[] = "-- Dumping data for `{$table}`";
                $columns = '`'.implode('`, `', array_keys($rows[0])).'`';
                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) {
                            return 'NULL';
                        }

                        return $pdo->quote((string) $val);
                    }, array_values($row));
                    $output[] = "INSERT INTO `{$table}` ({$columns}) VALUES (".implode(', ', $values).');';
                }
                $output[] = '';
            }
        }

        $output[] = "\nSET FOREIGN_KEY_CHECKS=1;";

        return implode("\n", $output);
    }

    /**
     * Get database size in MB.
     */
    public function databaseSizeMb(): float
    {
        $dbName = config('database.connections.'.config('database.default').'.database');
        $result = DB::selectOne(
            'SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
             FROM information_schema.TABLES
             WHERE table_schema = ?',
            [$dbName]
        );

        return (float) ($result?->size_mb ?? 0);
    }

    /**
     * Get list of backup files with metadata.
     */
    public function listFiles(): array
    {
        $dir = $this->backupDir();
        $files = glob($dir.DIRECTORY_SEPARATOR.'*.sql') ?: [];
        $out = [];
        foreach (array_reverse($files) as $file) {
            $out[] = [
                'filename' => basename($file),
                'size' => round(filesize($file) / 1024, 1).' KB',
                'modified' => date('Y-m-d H:i', filemtime($file)),
            ];
        }

        return $out;
    }

    /**
     * Delete a specific backup file by filename.
     */
    public function delete(string $filename): bool
    {
        $path = $this->backupDir().DIRECTORY_SEPARATOR.basename($filename);
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }

        return false;
    }
}
