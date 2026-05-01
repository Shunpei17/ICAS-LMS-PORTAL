<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentBulkImportService
{
    const DEFAULT_PASSWORD = '@icas_2026_12345';

    const BATCH_SIZE = 100;

    /**
     * Import students from CSV file.
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

        // Skip header row
        $header = fgetcsv($stream);
        $expectedColumns = ['Student Number', 'Full Name', 'Email', 'Academic Level'];

        if ($header !== $expectedColumns) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['CSV header mismatch. Expected: '.implode(', ', $expectedColumns)],
                'duplicates' => 0,
            ];
        }

        $lineNumber = 2;
        while (($row = fgetcsv($stream)) !== false) {
            if (empty(array_filter($row))) {
                $lineNumber++;

                continue;
            }

            [$studentNumber, $fullName, $email, $academicLevel] = array_pad($row, 4, '');

            // Validate row
            $validation = $this->validateRow($studentNumber, $fullName, $email, $academicLevel, $lineNumber);
            if (! $validation['valid']) {
                $failed++;
                $errors = array_merge($errors, $validation['errors']);
                $lineNumber++;

                continue;
            }

            // Check for duplicates (email or student_number)
            $existingUser = User::query()
                ->where('email', $email)
                ->orWhere('student_id', $studentNumber)
                ->first();

            if ($existingUser) {
                $duplicates++;
                $errors[] = "Line $lineNumber: Duplicate student (email: $email or student number: $studentNumber already exists)";
                $lineNumber++;

                continue;
            }

            // Queue for batch insert
            $batch[] = [
                'student_id' => $studentNumber,
                'name' => trim($fullName),
                'email' => trim($email),
                'academic_level' => trim($academicLevel),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'role' => 'student',
                'status' => 'active',
                'account_source' => 'csv_import',
                'force_password_change' => true,
                'imported_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Batch insert every BATCH_SIZE records
            if (count($batch) >= self::BATCH_SIZE) {
                $success += $this->insertBatch($batch);
                $batch = [];
            }

            $lineNumber++;
        }

        // Insert remaining batch
        if (! empty($batch)) {
            $success += $this->insertBatch($batch);
        }

        fclose($stream);

        Log::info('bulk_import_students', [
            'success' => $success,
            'failed' => $failed,
            'duplicates' => $duplicates,
            'errors_count' => count($errors),
        ]);

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
            'duplicates' => $duplicates,
        ];
    }

    /**
     * Validate a single CSV row.
     */
    private function validateRow(string $studentNumber, string $fullName, string $email, string $academicLevel, int $lineNumber): array
    {
        $errors = [];

        if (empty($studentNumber)) {
            $errors[] = "Line $lineNumber: Student Number is required.";
        }

        if (empty($fullName)) {
            $errors[] = "Line $lineNumber: Full Name is required.";
        }

        if (empty($email)) {
            $errors[] = "Line $lineNumber: Email is required.";
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Line $lineNumber: Email format is invalid ($email).";
        }

        $validLevels = ['Senior High School', '1st Year College', '2nd Year College', '3rd Year College'];
        if (empty($academicLevel)) {
            $errors[] = "Line $lineNumber: Academic Level is required.";
        } elseif (! in_array($academicLevel, $validLevels)) {
            $errors[] = "Line $lineNumber: Academic Level must be one of: ".implode(', ', $validLevels);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Insert a batch of users.
     */
    private function insertBatch(array $batch): int
    {
        try {
            User::query()->insert($batch);

            return count($batch);
        } catch (\Exception $e) {
            Log::error('bulk_import_batch_error', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * Generate CSV template content.
     */
    public static function generateTemplate(): string
    {
        $headers = ['Student Number', 'Full Name', 'Email', 'Academic Level'];
        $example = [
            ['STU-001', 'Juan Dela Cruz', 'juan.delacruz@school.edu', '1st Year College'],
            ['STU-002', 'Maria Santos', 'maria.santos@school.edu', '2nd Year College'],
            ['STU-003', 'Carlos Reyes', 'carlos.reyes@school.edu', 'Senior High School'],
        ];

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($example as $row) {
            fputcsv($output, $row);
        }
        fclose($output);

        return '';
    }
}
