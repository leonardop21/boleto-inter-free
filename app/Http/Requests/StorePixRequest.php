<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePixRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'my_number' => 'nullable|string|max:50',
            'due_date' => 'required|date|after_or_equal:today',
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
            'customer_id.required' => 'O cliente é obrigatório.',
            'customer_id.exists' => 'O cliente selecionado não existe.',
            'price.required' => 'O valor é obrigatório.',
            'price.numeric' => 'O valor deve ser um número.',
            'price.min' => 'O valor deve ser maior que zero.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'due_date.after_or_equal' => 'A data de vencimento deve ser igual ou posterior à data atual.',
        ];
    }
}
