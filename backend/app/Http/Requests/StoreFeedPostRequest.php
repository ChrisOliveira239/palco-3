<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedPostRequest extends FormRequest
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
            'fee_tipo' => ['required', Rule::in(Types::FEED_POST_TYPE)],
            'fee_conteudo' => ['nullable', 'string'],
            'fee_midia_url' => [
                Rule::requiredIf(in_array($this->input('fee_tipo'), ['FOTO', 'VIDEO'])),
                'nullable', 'url', 'max:255',
            ],
        ];
    }
}
