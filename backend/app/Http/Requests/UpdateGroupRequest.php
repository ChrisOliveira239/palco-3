<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
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
            'gro_nome' => ['sometimes', 'string', 'max:255'],
            'gro_descricao' => ['sometimes', 'nullable', 'string'],
            'gro_avatar_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'gro_capa_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'gro_cnpj' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gro_telefone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'gro_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'gro_site' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
