<?php

namespace App\Http\Requests\Role;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRoleRequest extends FormRequest
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
                'user_id' => 'required|exists:users,id',
                'role_id' => 'required|exists:role,id',
            ];
        }

        if($this->isMethod('put')){
            return [
                'user_id' => 'sometimes|required|exists:users,id',
                'role_id' => 'sometimes|required|exists:role,id',
            ];
        }

        return [];
    }
}
