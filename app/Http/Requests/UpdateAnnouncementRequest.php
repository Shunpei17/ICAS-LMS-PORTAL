<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'audience' => ['required', 'in:all,faculty,student'],
            'announcement_date' => ['required', 'date'],
            'attachment' => ['nullable', 'file', 'max:5120'],
            'remove_attachment' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for this announcement.',
            'content.required' => 'Please provide the announcement description.',
            'audience.required' => 'Please choose who can view this announcement.',
            'announcement_date.required' => 'Please choose an announcement date.',
            'attachment.max' => 'Attachment must not be larger than 5 MB.',
        ];
    }
}
