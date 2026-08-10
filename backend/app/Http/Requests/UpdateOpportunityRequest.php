<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityRequest extends FormRequest
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
            'opp_titulo' => ['sometimes', 'string', 'max:255'],
            'opp_descricao' => ['sometimes', 'string'],
            'skill_id' => ['sometimes', 'nullable', 'integer', 'exists:skills,id'],
            'opp_cidade' => ['sometimes', 'nullable', 'string', 'max:255'],
            'opp_status' => ['sometimes', Rule::in(Types::OPPORTUNITY_STATUS)],
        ];
    }
}
