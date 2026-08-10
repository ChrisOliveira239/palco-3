<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeedPostRequest extends FormRequest
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
            'fee_tipo' => ['sometimes', Rule::in(Types::FEED_POST_TYPE)],
            'fee_conteudo' => ['sometimes', 'nullable', 'string'],
            'fee_midia_url' => [
                Rule::requiredIf(in_array($this->input('fee_tipo'), ['FOTO', 'VIDEO'])),
                'sometimes', 'nullable', 'url', 'max:255',
            ],
        ];
    }
}
