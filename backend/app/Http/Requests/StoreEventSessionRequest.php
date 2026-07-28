<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventSessionRequest extends FormRequest
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
            'venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'evs_local_nome' => ['nullable', 'string', 'max:255'],
            'evs_endereco' => ['required_without:venue_id', 'nullable', 'string', 'max:255'],
            'evs_cidade' => ['required_without:venue_id', 'nullable', 'string', 'max:255'],
            'evs_estado' => ['required_without:venue_id', 'nullable', 'string', 'max:255'],
            'evs_data_inicio' => ['required', 'date'],
            'evs_data_fim' => ['nullable', 'date', 'after:evs_data_inicio'],
        ];
    }
}
