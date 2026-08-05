<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketTypeRequest extends FormRequest
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
            'tit_nome' => ['required', 'string', 'max:255'],
            'tit_preco' => ['required', 'numeric', 'min:0'],
            'tit_quantidade_total' => ['required', 'integer', 'min:1'],
            'tit_venda_inicio' => ['nullable', 'date'],
            'tit_venda_fim' => ['nullable', 'date', 'after_or_equal:tit_venda_inicio'],
        ];
    }
}
