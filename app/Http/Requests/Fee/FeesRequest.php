<?php

namespace App\Http\Requests\Fee;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FeesRequest extends FormRequest
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
                'name' => 'required',
                'amount' => 'required',
            ];
        }
        if($this->isMethod('put')){
            return [
                'name' => 'sometimes|required',
                'amount' => 'sometimes|required',
            ];
        }


        return [];
    }
}
