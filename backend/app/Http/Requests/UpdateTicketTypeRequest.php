<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketTypeRequest extends FormRequest
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
            'tit_nome' => ['sometimes', 'string', 'max:255'],
            'tit_preco' => ['sometimes', 'numeric', 'min:0'],
            'tit_quantidade_total' => [
                'sometimes', 'integer', 'min:1',
                function ($attribute, $value, $fail) {
                    $ticketType = $this->route('ticketType');

                    if ($value < $ticketType->tit_quantidade_vendida) {
                        $fail('A quantidade total não pode ser menor que a quantidade já vendida.');
                    }
                },
            ],
            'tit_venda_inicio' => ['nullable', 'date'],
            'tit_venda_fim' => ['nullable', 'date', 'after_or_equal:tit_venda_inicio'],
        ];
    }
}
