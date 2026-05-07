<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Grade;
use App\Services\GradingService;
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

            // Look up classroom-specific grading criteria weights
            $quizWeight = 0.30;
            $assignmentWeight = 0.30;
            $examWeight = 0.40;

            if ($classroom !== null) {
                $criteria = \App\Models\ClassroomGradingCriteria::where('classroom_id', $classroom->id)->get();
                if ($criteria->isNotEmpty()) {
                    $quizWeight = $criteria->where('component_name', 'Quiz')->first()?->weight / 100 ?? 0.30;
                    $assignmentWeight = $criteria->where('component_name', 'Assignment')->first()?->weight / 100 ?? 0.30;
                    $examWeight = $criteria->where('component_name', 'Exam')->first()?->weight / 100 ?? 0.40;
                }
            }

            // Compute weighted average
            $average = ($quiz * $quizWeight) + ($assignment * $assignmentWeight) + ($exam * $examWeight);

            // Use GradingService constant for pass/fail determination
            $remarks = $average >= GradingService::PASSING_GRADE ? 'Pass' : 'Fail';

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
            fputcsv($output, ['Student Name', 'Subject', 'Quiz', 'Assignment', 'Exam', 'Average', 'Remarks']);

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
