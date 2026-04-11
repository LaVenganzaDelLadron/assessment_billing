<?php

namespace App\Http\Requests\Year;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class YearRequest extends FormRequest
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

        if ($this->isMethod('post')){
            return [
                'year' => 'required',
            ];
        }
        if ($this->isMethod('put')){
            return [
                'year' => 'sometimes|required',
            ];
        }

        return [];
    }
}
