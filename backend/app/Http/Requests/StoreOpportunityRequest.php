<?php

namespace App\Http\Requests;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $id = (int) $this->input('criador_id');

        return match ($this->input('criador_type')) {
            'artist_profile' => $user->artistProfile?->id === $id,
            'group' => ($group = Group::find($id)) && ($user->isGroupOwner($group) || $user->isGroupAdmin($group)),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'opp_titulo' => ['required', 'string', 'max:255'],
            'opp_descricao' => ['required', 'string'],
            'criador_type' => ['required', 'string', Rule::in(['artist_profile', 'group'])],
            'criador_id' => ['required', 'integer'],
            'skill_id' => ['nullable', 'integer', 'exists:skills,id'],
            'opp_cidade' => ['nullable', 'string', 'max:255'],
        ];
    }
}
