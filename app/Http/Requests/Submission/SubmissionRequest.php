<?php

namespace App\Http\Requests\Submission;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes'];

        return [
            'assignment_id' => ['required', 'string', 'exists:assignments,id'],
            'student_id' => ['required', 'string', 'exists:users,id'],
            'content' => ['required', 'string'],
            'status' => ['sometimes', 'in:submitted,graded,late,pending'],
        ];
    }
}
