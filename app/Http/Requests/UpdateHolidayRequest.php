<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends FormRequest
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
        $holidayId = $this->route('id') ?? $this->route('holiday');

        return [
            'name' => 'required|string|max:255',
            'date' => [
                'required',
                'date',
                Rule::unique('holidays', 'date')->ignore($holidayId),
            ],
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
