<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArtistProfileRequest extends FormRequest
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
            'art_nome_artistico' => ['sometimes', 'string', 'max:255'],
            'art_bio' => ['sometimes', 'nullable', 'string'],
            'art_capa_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'art_drt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'art_telefone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'art_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'art_site' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
