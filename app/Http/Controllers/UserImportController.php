<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header
        fgetcsv($handle);

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($row) < 3) {
                continue;
            }

            $studentNumber = $row[0];
            $fullName = $row[1];
            $email = $row[2];

            User::updateOrCreate(
                ['email' => $email],
                [
                    'student_number' => $studentNumber,
                    'name' => $fullName,
                    'password' => Hash::make('icas_default123'),
                    'role' => 'student',
                    'onboarding_source' => 'csv',
                    'enrollment_type' => 'old_student',
                    'is_verified' => true,
                    'needs_password_change' => true,
                    'status' => 'active',
                ]
            );
        }

        fclose($handle);

        return redirect()->back()->with('status', 'Users imported successfully.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=user_import_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['student_number', 'full_name', 'email'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
