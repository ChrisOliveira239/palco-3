<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcceptedSupportTypeRequest extends FormRequest
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
        $alvo = $this->route('event') ?? $this->route('artistProfile') ?? $this->route('group');

        return [
            'ast_tipo_apoio' => [
                'required',
                Rule::in(Types::TIPO_APOIO),
                Rule::unique('accepted_support_types')->where(
                    fn ($query) => $query->where('alvo_type', get_class($alvo))->where('alvo_id', $alvo->id)
                ),
            ],
        ];
    }
}
