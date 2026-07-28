<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'venue_id' => ['sometimes', 'nullable', 'integer', 'exists:venues,id'],
            'evs_local_nome' => ['sometimes', 'nullable', 'string', 'max:255'],
            'evs_endereco' => ['sometimes', 'nullable', 'string', 'max:255'],
            'evs_cidade' => ['sometimes', 'nullable', 'string', 'max:255'],
            'evs_estado' => ['sometimes', 'nullable', 'string', 'max:255'],
            'evs_data_inicio' => ['sometimes', 'date'],
            'evs_data_fim' => ['sometimes', 'nullable', 'date', 'after:evs_data_inicio'],
        ];
    }
}
