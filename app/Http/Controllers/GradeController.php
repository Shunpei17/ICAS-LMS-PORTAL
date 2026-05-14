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

            $classroom = Classroom::where('code', $data['subject_id'])->first();
            if ($classroom !== null && ! auth()->user()->can('manage', $classroom)) {
                $skipped[] = $data['subject_id'];
                continue;
            }

            $components = $data['components'] ?? [];
            $average = 0;
            $criteria = $classroom ? \App\Models\ClassroomGradingCriteria::where('classroom_id', $classroom->id)->get() : collect();

            if ($criteria->isNotEmpty()) {
                foreach ($criteria as $criterion) {
                    $compKey = strtolower(str_replace(' ', '_', $criterion->component_name));
                    $score = (float) ($components[$compKey] ?? 0);
                    $average += ($score * ($criterion->weight / 100));
                }
            } else {
                // Fallback to legacy structure if no criteria defined
                $quiz = (float) ($data['quiz'] ?? 0);
                $assignment = (float) ($data['assignment'] ?? 0);
                $exam = (float) ($data['exam'] ?? 0);
                $average = ($quiz * 0.3) + ($assignment * 0.3) + ($exam * 0.4);
                $components = ['quiz' => $quiz, 'assignment' => $assignment, 'exam' => $exam];
            }

            $remarks = $average >= GradingService::PASSING_GRADE ? 'Pass' : 'Fail';

            Grade::updateOrCreate(
                [
                    'student_id' => $data['student_id'],
                    'subject_id' => $data['subject_id'],
                ],
                [
                    'component_scores' => $components,
                    'average' => $average,
                    'remarks' => $remarks,
                    // keep legacy columns updated for compatibility if they exist in components
                    'quiz' => $components['quiz'] ?? ($data['quiz'] ?? 0),
                    'assignment' => $components['assignment'] ?? ($data['assignment'] ?? 0),
                    'exam' => $components['exam'] ?? ($data['exam'] ?? 0),
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
