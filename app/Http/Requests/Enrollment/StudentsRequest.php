<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentsRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|max:255',
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
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name' => 'sometimes|required|string|max:255',
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
            ];
        }

        return [];
    }
}
