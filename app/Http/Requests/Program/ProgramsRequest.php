<?php

namespace App\Http\Requests\Program;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProgramsRequest extends FormRequest
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
        if($this->isMethod('post')) {
            return [
                'name' => 'required',
                'description' => 'required',
                'school_id' => 'required|exists:school,id',
            ];
        }

        if($this->isMethod('put')){
            return [
                'name' => 'sometimes|required',
                'description' => 'sometimes|required',
                'school_id' => 'sometimes|required|exists:school,id',
            ];
        }

        return [];
    }
}
