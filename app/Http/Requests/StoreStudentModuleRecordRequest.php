<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentModuleRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user() === null || $this->user()->role !== 'student') {
            return false;
        }

        $moduleCode = $this->input('module_code');
        if (! $moduleCode) {
            return true;
        }

        $classroom = \App\Models\Classroom::where('code', $moduleCode)->first();
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
            'record_id' => ['nullable', 'integer'],
            'module_name' => ['required', 'string', 'max:255'],
            'module_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('student_module_records', 'module_code')
                    ->where(fn ($query) => $query->where('user_id', $this->user()?->id))
                    ->ignore((int) $this->input('record_id')),
            ],
            'instructor' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'grade_percent' => ['nullable', 'numeric', 'between:0,100'],
            'documents_count' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'upcoming_assessment_title' => ['nullable', 'string', 'max:255'],
            'upcoming_assessment_points' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'upcoming_assessment_due_date' => ['nullable', 'date'],
            'upcoming_assessment_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'module_name.required' => 'Please provide the module name.',
            'module_code.required' => 'Please provide the module code.',
            'module_code.unique' => 'This module code already exists in your records.',
            'grade_percent.between' => 'Grade percent must be between 0 and 100.',
            'documents_count.min' => 'Documents count cannot be negative.',
        ];
    }
}
