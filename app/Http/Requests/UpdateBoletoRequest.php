<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoletoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => 'required|in:EM ABERTO,PAGO,VENCIDO,CANCELADO',
            'payment_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: EM ABERTO, PAGO, VENCIDO ou CANCELADO.',
            'payment_date.date' => 'A data de pagamento deve ser uma data válida.',
        ];
    }
}
