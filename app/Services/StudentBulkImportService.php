<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentBulkImportService
{
    const STUDENT_DEFAULT_PASSWORD = 'Icas_Students@2026';
    const FACULTY_DEFAULT_PASSWORD = 'Icas_Faculty@2026';
    const ADMIN_DEFAULT_PASSWORD = 'Icas_admin@2026';

    const BATCH_SIZE = 100;

    /**
     * Import users from CSV file.
     *
     * @return array{success: int, failed: int, errors: array, duplicates: int}
     */
    public function import(UploadedFile $file): array
    {
        $stream = fopen($file->getRealPath(), 'r');
        if (! $stream) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['Failed to open file'], 'duplicates' => 0];
        }

        $success = 0;
        $failed = 0;
        $duplicates = 0;
        $errors = [];
        $batch = [];

        // Read header row
        $header = fgetcsv($stream);

        $studentColumns = ['Student Number', 'Full Name', 'Email', 'Academic Level', 'Course', 'Strand'];
        $facultyColumns = ['Faculty Unique Number', 'Full Name', 'Email', 'Department'];
        $adminColumns = ['Admin unique number', 'Full Name', 'Email', 'Department'];

        // Trim headers just in case of BOM or whitespace
        $header = array_map('trim', $header);

        $type = null;
        if ($header === $studentColumns) {
            $type = 'student';
        } elseif ($header === $facultyColumns) {
            $type = 'faculty';
        } elseif ($header === $adminColumns) {
            $type = 'admin';
        }

        if (! $type) {
            fclose($stream);

            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['CSV header mismatch. Please use the provided templates.'],
                'duplicates' => 0,
            ];
        }

        $lineNumber = 2;
        while (($row = fgetcsv($stream)) !== false) {
            if (empty(array_filter($row))) {
                $lineNumber++;
                continue;
            }

            if ($type === 'student') {
                [$number, $name, $email, $level, $course, $strand] = array_pad($row, 6, '');
                $validation = $this->validateStudent($number, $name, $email, $level, $lineNumber);
                $role = 'student';
                $password = self::STUDENT_DEFAULT_PASSWORD;
                $extra = [
                    'student_number' => $number,
                    'academic_level' => trim($level),
                    'course' => trim($course),
                    'strand' => trim($strand),
                ];
            } elseif ($type === 'faculty') {
                [$number, $name, $email, $dept] = array_pad($row, 4, '');
                $validation = $this->validateFaculty($number, $name, $email, $lineNumber);
                $role = 'faculty';
                $password = self::FACULTY_DEFAULT_PASSWORD;
                $extra = [
                    'department' => trim($dept),
                ];
            } else {
                [$number, $name, $email, $dept] = array_pad($row, 4, '');
                $validation = $this->validateAdmin($number, $name, $email, $lineNumber);
                $role = 'admin';
                $password = self::ADMIN_DEFAULT_PASSWORD;
                $extra = [
                    'admin_number' => $number,
                    'department' => trim($dept),
                ];
            }

            if (! $validation['valid']) {
                $failed++;
                $errors = array_merge($errors, $validation['errors']);
                $lineNumber++;
                continue;
            }

            // Check for duplicates
            $existing = User::where('email', $email)
                ->when($type === 'student', fn ($q) => $q->orWhere('student_number', $number))
                ->when($type === 'admin', fn ($q) => $q->orWhere('admin_number', $number))
                ->exists();

            if ($existing) {
                $duplicates++;
                $errors[] = "Line $lineNumber: Duplicate account (Email or ID already exists)";
                $lineNumber++;
                continue;
            }

            $batch[] = array_merge([
                'name' => trim($name),
                'email' => trim($email),
                'password' => Hash::make($password),
                'role' => $role,
                'status' => 'active',
                'is_verified' => true,
                'force_password_change' => true,
                'account_source' => 'csv_import',
                'imported_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ], $extra);

            if (count($batch) >= self::BATCH_SIZE) {
                $success += $this->insertBatch($batch);
                $batch = [];
            }

            $lineNumber++;
        }

        if (! empty($batch)) {
            $success += $this->insertBatch($batch);
        }

        fclose($stream);

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
            'duplicates' => $duplicates,
        ];
    }

    private function validateStudent($number, $name, $email, $level, $line): array
    {
        $errs = [];
        if (empty($number)) $errs[] = "Line $line: Student Number is required.";
        if (empty($name)) $errs[] = "Line $line: Full Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = "Line $line: Valid Email is required.";
        
        $validLevels = ['Senior High School', '1st Year College', '2nd Year College', '3rd Year College'];
        if (empty($level) || !in_array($level, $validLevels)) $errs[] = "Line $line: Valid Academic Level is required.";

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    private function validateFaculty($number, $name, $email, $line): array
    {
        $errs = [];
        if (empty($number)) $errs[] = "Line $line: Faculty Number is required.";
        if (empty($name)) $errs[] = "Line $line: Full Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = "Line $line: Valid Email is required.";

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    private function validateAdmin($number, $name, $email, $line): array
    {
        $errs = [];
        if (empty($number)) $errs[] = "Line $line: Admin Number is required.";
        if (empty($name)) $errs[] = "Line $line: Full Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = "Line $line: Valid Email is required.";

        return ['valid' => empty($errs), 'errors' => $errs];
    }

    private function insertBatch(array $batch): int
    {
        try {
            User::insert($batch);
            return count($batch);
        } catch (\Exception $e) {
            Log::error('Bulk Import Error: ' . $e->getMessage());
            return 0;
        }
    }
}

