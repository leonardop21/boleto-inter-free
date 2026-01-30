<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
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
            'name.required' => 'O nome do feriado é obrigatório.',
            'name.max' => 'O nome do feriado não pode ter mais de 255 caracteres.',
            'date.required' => 'A data do feriado é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'date.unique' => 'Já existe um feriado cadastrado para esta data.',
        ];
    }
}
