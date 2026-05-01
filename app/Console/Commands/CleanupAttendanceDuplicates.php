<?php

namespace App\Console\Commands;

use App\Models\FacultyAttendanceRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupAttendanceDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup-duplicates {--dry-run} {--keep=latest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identify and remove duplicate attendance records based on (student, class, date, faculty).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $keep = $this->option('keep'); // 'latest', 'earliest', or 'first'

        $this->info('Scanning for duplicate attendance records (dry-run: '.($dryRun ? 'yes' : 'no').')');

        // Find all groups with duplicates: same student_user_id, student_class, attendance_date, faculty_user_id
        $duplicateGroups = FacultyAttendanceRecord::query()
            ->select('student_user_id', 'student_name', 'student_class', 'attendance_date', 'faculty_user_id')
            ->groupByRaw('student_user_id, student_name, student_class, attendance_date, faculty_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateGroups->isEmpty()) {
            $this->info('No duplicates found.');

            return 0;
        }

        $this->warn("Found {$duplicateGroups->count()} duplicate groups.");

        $totalDeleted = 0;
        foreach ($duplicateGroups as $group) {
            $query = FacultyAttendanceRecord::query()
                ->where('student_user_id', $group->student_user_id)
                ->where('student_class', $group->student_class)
                ->whereDate('attendance_date', $group->attendance_date)
                ->where('faculty_user_id', $group->faculty_user_id);

            $records = $query->get()->sortBy('id');
            $count = $records->count();

            // Determine which record to keep
            if ($keep === 'latest') {
                $toKeep = $records->last();
            } elseif ($keep === 'earliest') {
                $toKeep = $records->first();
            } else {
                // Default: keep first, delete rest
                $toKeep = $records->first();
            }

            $toDelete = $records->filter(fn ($r) => $r->id !== $toKeep->id)->pluck('id')->all();

            $this->line("Group: {$group->student_name} ({$group->student_class}) on {$group->attendance_date} — {$count} total, keeping id {$toKeep->id}, deleting ".count($toDelete));
            Log::info('attendance:cleanup:duplicates', [
                'group' => $group->toArray(),
                'total' => $count,
                'keep_id' => $toKeep->id,
                'delete_ids' => $toDelete,
            ]);

            if (! $dryRun && ! empty($toDelete)) {
                FacultyAttendanceRecord::query()->whereIn('id', $toDelete)->delete();
                $totalDeleted += count($toDelete);
            }
        }

        $this->info("Total records deleted: {$totalDeleted} (dry-run: ".($dryRun ? 'yes' : 'no').')');

        return 0;
    }
}
