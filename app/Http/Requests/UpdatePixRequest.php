<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePixRequest extends FormRequest
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
            'status' => 'required|in:ATIVA,CONCLUIDA,VENCIDO,CANCELADA',
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
            'status.in' => 'O status deve ser: ATIVA, CONCLUIDA, VENCIDO ou CANCELADA.',
            'payment_date.date' => 'A data de pagamento deve ser uma data válida.',
        ];
    }
}
