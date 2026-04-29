<?php

namespace App\Exports;

use Illuminate\Collections\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClassroomStudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $students;

    public function __construct(Collection $students)
    {
        $this->students = $students;
    }

    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
        return ['Student Number', 'Full Name', 'Academic Level', 'Enrollment Status', 'Email', 'Enrolled At'];
    }

    public function map($student): array
    {
        return [
            $student->student_number ?? '',
            $student->name ?? '',
            $student->academic_level ?? '',
            $student->pivot->enrollment_status ?? '',
            $student->email ?? '',
            $student->pivot->enrolled_at ?? '',
        ];
    }
}
