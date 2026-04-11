<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EnrollmentsRequest extends FormRequest
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
        if($this->isMethod('post')) {
            return [
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:classes,id',
                'year_id' => 'required|exists:year_level,id',
            ];
        }

        if($this->isMethod('put')){
            return [
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:classes,id',
                'year_id' => 'required|exists:year_level,id',
            ];
        }

        return [];
    }
}
