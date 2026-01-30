<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerTypeNfeRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:customer_nfe_types,name',
            'description' => 'nullable|string|max:500',
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
            'name.required' => 'O nome do tipo de nota é obrigatório.',
            'name.max' => 'O nome do tipo de nota não pode ter mais de 255 caracteres.',
            'name.unique' => 'Já existe um tipo de nota com este nome.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
        ];
    }
}
