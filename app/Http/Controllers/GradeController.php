<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Grade;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeController extends Controller
{
    public function store(Request $request)
    {
        $gradesData = $request->input('grades', []);
        $skipped = [];

        foreach ($gradesData as $data) {
            if (empty($data['student_id']) || empty($data['subject_id'])) {
                continue;
            }

            $quiz = $data['quiz'] ?? 0;
            $assignment = $data['assignment'] ?? 0;
            $exam = $data['exam'] ?? 0;

            // Skip if subject maps to an inactive classroom
            $classroom = Classroom::where('code', $data['subject_id'])->first();
            if ($classroom !== null) {
                // Use policy to ensure user can manage this classroom (and it's active)
                if (! auth()->user()->can('manage', $classroom)) {
                    $skipped[] = $data['subject_id'];

                    continue;
                }
            }

            // Compute
            // Quiz (30%), Assignment (30%), Exam (40%)
            $average = ($quiz * 0.30) + ($assignment * 0.30) + ($exam * 0.40);
            $remarks = $average >= 75 ? 'Pass' : 'Fail';

            Grade::updateOrCreate(
                [
                    'student_id' => $data['student_id'],
                    'subject_id' => $data['subject_id'],
                ],
                [
                    'quiz' => $quiz,
                    'assignment' => $assignment,
                    'exam' => $exam,
                    'average' => $average,
                    'remarks' => $remarks,
                ]
            );
        }

        $message = 'Grades saved successfully!';
        if (count($skipped) > 0) {
            $message = 'Some grades were skipped because their classroom is inactive: '.implode(', ', array_unique($skipped));
        }

        return redirect()->back()->with('status', $message);
    }

    public function export(Request $request): StreamedResponse
    {
        $subjectId = $request->query('grade_subject', '');

        $query = Grade::with('student');
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        $grades = $query->get();

        $filename = 'grades-export-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($grades) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Student Name', 'Subject', 'Quiz (30%)', 'Assignment (30%)', 'Exam (40%)', 'Average', 'Remarks']);

            foreach ($grades as $grade) {
                fputcsv($output, [
                    $grade->student ? $grade->student->name : 'Unknown',
                    $grade->subject_id,
                    $grade->quiz,
                    $grade->assignment,
                    $grade->exam,
                    $grade->average,
                    $grade->remarks,
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
