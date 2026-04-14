<?php

namespace App\Http\Requests\Subject;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'subject_code' => 'required|string|max:255',
                'subject_name' => 'required|string|max:255',
                'units' => 'required|integer|min:1|max:99',
                'type' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'program_id' => [
                    'nullable',
                    'string',
                    'required_without:program_code',
                    Rule::exists('programs', 'id'),
                ],
                'program_code' => [
                    'nullable',
                    'string',
                    'required_without:program_id',
                    Rule::exists('programs', 'code'),
                ],
                'year_level' => 'nullable|integer|min:1|max:10',
                'semester' => 'nullable|string|max:20',
                'school_year' => 'nullable|string|max:20',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'subject_code' => 'sometimes|required|string|max:255',
                'subject_name' => 'sometimes|required|string|max:255',
                'units' => 'sometimes|required|integer|min:1|max:99',
                'type' => 'sometimes|nullable|string|max:255',
                'status' => 'sometimes|nullable|string|max:255',
                'program_id' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'required_without:program_code',
                    Rule::exists('programs', 'id'),
                ],
                'program_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'required_without:program_id',
                    Rule::exists('programs', 'code'),
                ],
                'year_level' => 'sometimes|nullable|integer|min:1|max:10',
                'semester' => 'sometimes|nullable|string|max:20',
                'school_year' => 'sometimes|nullable|string|max:20',
            ];
        }

        return [];
    }
}
