<?php

namespace App\Console\Commands;

use App\Models\FacultyAttendanceRecord;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MapAttendanceStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:map-students {--dry-run} {--threshold=70} {--limit=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map faculty_attendance_records.student_name to users.id using email/name/fuzzy matching (best-effort).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $threshold = (int) $this->option('threshold');
        $limit = (int) $this->option('limit');

        $this->info('Loading users...');
        $users = User::query()->get(['id', 'name', 'email'])->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => trim((string) $u->name),
                'email' => trim((string) $u->email),
            ];
        })->all();

        $query = FacultyAttendanceRecord::query()->whereNull('student_user_id');
        if ($limit > 0) {
            $query->limit($limit);
        }
        $rows = $query->get();

        $this->info('Processing '.count($rows).' attendance rows (dry-run: '.($dryRun ? 'yes' : 'no').')');

        foreach ($rows as $row) {
            $studentName = trim((string) $row->student_name);
            $mapped = null;

            // 1) Extract embedded email like "Name (email@domain)"
            if (preg_match('/\(([^)@\s]+@[^)@\s]+)\)/', $studentName, $m)) {
                $email = $m[1];
                $user = collect($users)->firstWhere('email', $email);
                if ($user) {
                    $mapped = ['method' => 'embedded_email', 'user' => $user];
                }
            }

            // 2) Exact email match
            if ($mapped === null) {
                $user = collect($users)->firstWhere('email', $studentName);
                if ($user) {
                    $mapped = ['method' => 'exact_email', 'user' => $user];
                }
            }

            // 3) Exact name match (case-insensitive)
            if ($mapped === null) {
                $user = collect($users)->first(function ($u) use ($studentName) {
                    return strcasecmp($u['name'], $studentName) === 0;
                });
                if ($user) {
                    $mapped = ['method' => 'exact_name', 'user' => $user];
                }
            }

            // 4) Fuzzy name match using similar_text percent
            if ($mapped === null) {
                $best = null;
                $bestPercent = 0;
                foreach ($users as $u) {
                    similar_text(strtolower($studentName), strtolower($u['name']), $percent);
                    if ($percent > $bestPercent) {
                        $bestPercent = $percent;
                        $best = $u;
                    }
                }

                if ($best !== null && $bestPercent >= $threshold) {
                    $mapped = ['method' => 'fuzzy_name', 'user' => $best, 'score' => $bestPercent];
                }
            }

            if ($mapped) {
                $userId = $mapped['user']['id'];
                $this->line("Row {$row->id}: matched via {$mapped['method']} to user {$mapped['user']['name']} ({$userId})");
                Log::info('attendance:map', ['row' => $row->id, 'method' => $mapped['method'], 'user_id' => $userId, 'student_name' => $studentName]);

                if (! $dryRun) {
                    $row->student_user_id = $userId;
                    $row->save();
                }
                continue;
            }

            $this->line("Row {$row->id}: no confident match for '{$studentName}'");
            Log::warning('attendance:map:nomatch', ['row' => $row->id, 'student_name' => $studentName]);
        }

        $this->info('Done.');
        return 0;
    }
}
