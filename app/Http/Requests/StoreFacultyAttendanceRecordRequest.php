<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacultyAttendanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user() === null || $this->user()->role !== 'faculty') {
            return false;
        }

        $classCode = $this->input('student_class');
        if (! $classCode) {
            return true;
        }

        $classroom = \App\Models\Classroom::where('code', $classCode)->first();
        if ($classroom !== null && $classroom->status !== 'active') {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_name' => ['required', 'string', 'max:255'],
            'student_class' => ['required', 'string', 'max:50'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'in:Present,Absent,Late'],
            // optional flag: if true, update existing record instead of blocking
            'update_if_exists' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_name.required' => 'Please provide the student name.',
            'student_class.required' => 'Please provide the class.',
            'attendance_date.required' => 'Please select an attendance date.',
            'status.required' => 'Please select an attendance status.',
        ];
    }
}
