<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArtistProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()->isArtist();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'art_nome_artistico' => ['required', 'string', 'max:255'],
            'art_bio' => ['nullable', 'string'],
            'art_capa_url' => ['nullable', 'string', 'max:2048'],
            'art_drt' => ['nullable', 'string', 'max:255'],
            'art_telefone' => ['nullable', 'string', 'max:30'],
            'art_email' => ['nullable', 'email', 'max:255'],
            'art_site' => ['nullable', 'string', 'max:255'],
        ];
    }
}
