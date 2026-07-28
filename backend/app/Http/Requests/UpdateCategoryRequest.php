<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
            'cat_nome' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('categories', 'cat_nome')->ignore($this->route('category')),
            ],
            'cat_icone' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
