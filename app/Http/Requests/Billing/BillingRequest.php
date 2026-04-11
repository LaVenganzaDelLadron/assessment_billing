<?php

namespace App\Http\Requests\Billing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
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
                'student_id' => 'required',
                'fee_id' => 'required',
                'total_amount' => 'required',
                'status' => 'sometimes|required',
                'billing_date' => 'required|date',
                'due_date' => 'required|date',
            ];
        }
        if($this->isMethod('put')){
            return [
                'student_id' => 'sometimes|required',
                'fee_id' => 'sometimes|required',
                'total_amount' => 'sometimes|required',
                'status' => 'sometimes|required',
                'billing_date' => 'sometimes|required|date',
                'due_date' => 'sometimes|required|date',
            ];
        }
        return [];
    }
}
