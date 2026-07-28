<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueRequest extends FormRequest
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
            'ven_nome' => ['sometimes', 'string', 'max:255'],
            'ven_endereco' => ['sometimes', 'string', 'max:255'],
            'ven_cidade' => ['sometimes', 'string', 'max:255'],
            'ven_estado' => ['sometimes', 'string', 'max:255'],
            'ven_latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'ven_longitude' => ['sometimes', 'numeric', 'between:-180,180'],
        ];
    }
}
