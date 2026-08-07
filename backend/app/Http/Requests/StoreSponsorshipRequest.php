<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorshipRequest extends FormRequest
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
        $aceitos = $alvo->acceptedSupportTypes()->pluck('ast_tipo_apoio')->all();

        return [
            'spo_tipo_apoio' => [
                'required',
                Rule::in(Types::TIPO_APOIO),
                function ($attribute, $value, $fail) use ($aceitos) {
                    if ($aceitos !== [] && ! in_array($value, $aceitos, true)) {
                        $fail('Esse alvo não aceita esse tipo de apoio.');
                    }
                },
            ],
            'spo_valor' => ['required_if:spo_tipo_apoio,DINHEIRO', 'nullable', 'numeric', 'min:0.01'],
            'spo_descricao' => ['nullable', 'string'],
        ];
    }
}
