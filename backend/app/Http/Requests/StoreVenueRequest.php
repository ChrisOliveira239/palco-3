<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
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
            'ven_nome' => ['required', 'string', 'max:255'],
            'ven_endereco' => ['required', 'string', 'max:255'],
            'ven_cidade' => ['required', 'string', 'max:255'],
            'ven_estado' => ['required', 'string', 'max:255'],
            'ven_latitude' => ['required', 'numeric', 'between:-90,90'],
            'ven_longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}