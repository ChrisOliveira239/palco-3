<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'eve_titulo' => ['sometimes', 'string', 'max:255'],
            'eve_descricao' => ['sometimes', 'string'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'eve_gratuito' => ['sometimes', 'boolean'],
            'eve_cartaz_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'eve_links_externos' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
