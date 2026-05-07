<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\BackupService;
use App\Services\SystemSettingsService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaintenanceController extends Controller
{
    public function __construct(
        private BackupService $backup,
        private SystemSettingsService $settings
    ) {}

    /**
     * Main maintenance & backup dashboard.
     */
    public function index(): View
    {
        $scheduledBackup = $this->settings->get('backup_schedule', 'none');
        $lastBackup = null;
        $backupHistory = collect();

        try {
            $lastBackup = BackupLog::latest()->first();
            $backupHistory = BackupLog::latest()->take(20)->get();
        } catch (QueryException $e) {
            // Table doesn't exist yet, gracefully handle
        }

        $dbSizeMb = $this->backup->databaseSizeMb();
        $backupFiles = $this->backup->listFiles();

        // System health checks
        $dbConnected = true;
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        $backupDirWritable = is_writable(storage_path('app'));
        $storageUsedMb = $this->storageUsedMb();

        $healthChecks = [
            ['label' => 'Database Connection', 'ok' => $dbConnected,        'detail' => $dbConnected ? 'MySQL connected' : 'Connection failed'],
            ['label' => 'Backup Storage',       'ok' => $backupDirWritable, 'detail' => $backupDirWritable ? 'Directory writable' : 'Storage not writable'],
        ];

        return view('admin.maintenance', compact(
            'scheduledBackup',
            'lastBackup',
            'dbSizeMb',
            'backupFiles',
            'backupHistory',
            'healthChecks',
            'storageUsedMb'
        ));
    }

    /**
     * Trigger an immediate manual backup and download it.
     */
    public function backup(): BinaryFileResponse|RedirectResponse
    {
        try {
            $filepath = $this->backup->generate(
                initiatedBy: auth()->user()->name ?? 'Admin',
                type: 'manual'
            );

            return response()->download($filepath, basename($filepath), [
                'Content-Type' => 'application/sql',
            ]);
        } catch (\Throwable $e) {
            BackupLog::create([
                'filename' => 'failed_'.date('Ymd_His').'.sql',
                'size_bytes' => 0,
                'initiated_by' => auth()->user()->name ?? 'Admin',
                'type' => 'manual',
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);

            return back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    /**
     * Download an existing backup file by filename.
     */
    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        $path = storage_path('app/backups/'.basename($filename));

        if (! file_exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        return response()->download($path, basename($filename));
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(Request $request): RedirectResponse
    {
        $filename = $request->input('filename');
        $deleted = $this->backup->delete($filename);

        if ($deleted) {
            BackupLog::where('filename', basename($filename))->delete();

            return back()->with('status', 'Backup file deleted.');
        }

        return back()->with('error', 'Could not delete backup file.');
    }

    /**
     * Upload a .sql file and restore the database.
     * ⚠ Destructive operation — admin confirmation required.
     */
    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:51200',
        ]);

        try {
            $sql = file_get_contents($request->file('backup_file')->getPathname());

            // Execute the SQL statements via PDO for safety
            $config = config('database.connections.'.config('database.default'));
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new \PDO($dsn, $config['username'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

            // Split on semicolons, filtering empty statements
            $statements = array_filter(
                array_map('trim', explode(";\n", $sql)),
                fn ($s) => strlen($s) > 3
            );

            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

            BackupLog::create([
                'filename' => $request->file('backup_file')->getClientOriginalName(),
                'size_bytes' => $request->file('backup_file')->getSize(),
                'initiated_by' => auth()->user()->name ?? 'Admin',
                'type' => 'restore',
                'status' => 'success',
                'notes' => 'Database restored from uploaded backup.',
            ]);

            return back()->with('status', '✅ Database restored successfully from backup file.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Restore failed: '.$e->getMessage());
        }
    }

    /**
     * Update the backup schedule setting.
     */
    public function updateSchedule(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_schedule' => 'required|in:none,daily,weekly',
        ]);

        $this->settings->set('backup_schedule', $request->input('backup_schedule'));

        return back()->with('status', 'Backup schedule updated.');
    }

    /**
     * Approximate storage used by the backups folder in MB.
     */
    private function storageUsedMb(): float
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            return 0.0;
        }

        $total = 0;
        foreach (glob($dir.'/*.sql') ?: [] as $file) {
            $total += filesize($file);
        }

        return round($total / 1024 / 1024, 2);
    }
}
