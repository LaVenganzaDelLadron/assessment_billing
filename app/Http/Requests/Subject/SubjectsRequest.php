<?php

namespace App\Http\Requests\Subject;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        if($this->isMethod('post')){
            return [
                'code' => 'required',
                'name' => 'required',
                'class_id' => 'required',
            ];
        }
        if($this->isMethod('put')){
            return [
                'code' => 'sometimes|required',
                'name' => 'sometimes|required',
                'class_id' => 'sometimes|required',
            ];
        }

        return [];
    }
}
