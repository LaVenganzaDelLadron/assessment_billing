<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentsRequest extends FormRequest
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
                'billing_id' => 'required',
                'amount' => 'required',
                'method' => 'required',
                'payment_date' => 'required',
            ];
        }
        if($this->isMethod('put')) {
            return [
                'billing_id' => 'sometimes|required',
                'amount' => 'sometimes|required',
                'method' => 'sometimes|required',
                'payment_date' => 'sometimes|required',
            ];
        }

        return [];
    }
}
