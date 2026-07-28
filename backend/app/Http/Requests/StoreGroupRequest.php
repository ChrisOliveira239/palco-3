<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'gro_nome' => ['required', 'string', 'max:255'],
            'gro_descricao' => ['nullable', 'string'],
            'gro_avatar_url' => ['nullable', 'string', 'max:2048'],
            'gro_capa_url' => ['nullable', 'string', 'max:2048'],
            'gro_cnpj' => ['nullable', 'string', 'max:255'],
            'gro_telefone' => ['nullable', 'string', 'max:30'],
            'gro_email' => ['nullable', 'email', 'max:255'],
            'gro_site' => ['nullable', 'string', 'max:255'],
        ];
    }
}
