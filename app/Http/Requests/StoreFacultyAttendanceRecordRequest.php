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
        return $this->user() !== null && $this->user()->role === 'faculty';
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
