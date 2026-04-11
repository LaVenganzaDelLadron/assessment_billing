<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SchoolRequest extends FormRequest
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
                'name' => 'required',
                'email' => 'required|email',
                'address' => 'required',
                'contact' => 'required',
            ];
        }

        if($this->isMethod('put')){
            return [
                'name' => 'sometimes|required',
                'email' => 'sometimes|required',
                'address' => 'sometimes|required',
                'contact' => 'sometimes|required',
            ];
        }

        return [];
    }
}
