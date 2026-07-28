<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'cat_nome' => ['required', 'string', 'max:255', 'unique:categories,cat_nome'],
            'cat_icone' => ['nullable', 'string', 'max:255'],
        ];
    }
}
