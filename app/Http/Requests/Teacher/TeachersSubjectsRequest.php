<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


class TeachersSubjectsRequest extends FormRequest
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

        if($this->isMethod('post')){
            return [
                'teacher_id' => 'required',
                'subject_id' => 'required',
            ];
        }

        if($this->isMethod('put')){
            return [
                'teacher_id' => 'sometimes|required',
                'subject_id' => 'sometimes|required',
            ];
        }


        return [];
    }
}
