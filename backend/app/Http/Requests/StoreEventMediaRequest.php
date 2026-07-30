<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventMediaRequest extends FormRequest
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
            'evm_tipo' => ['required', Rule::in(Types::EVENT_MEDIA_TYPE)],
            'evm_url' => ['required', 'url', 'max:255'],
            'evm_ordem' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
