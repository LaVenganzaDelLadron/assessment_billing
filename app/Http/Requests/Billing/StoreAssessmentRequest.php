<?php

namespace App\Http\Requests\Billing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
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
        return [
            'student_id' => ['required', 'string', 'exists:users,id'],
            'total_units' => ['required', 'numeric', 'min:0'],
            'miscellaneous_fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'The student_id field is required.',
            'student_id.exists' => 'The selected student_id is invalid.',
            'total_units.required' => 'The total_units field is required.',
            'total_units.numeric' => 'The total_units must be a valid number.',
            'miscellaneous_fee.numeric' => 'The miscellaneous_fee must be a valid number.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('studentId') && ! $this->filled('student_id')) {
            $this->merge([
                'student_id' => $this->route('studentId'),
            ]);
        }
    }
}
