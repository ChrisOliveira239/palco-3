<?php

namespace App\Http\Requests;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $id = (int) $this->input('organizador_id');

        return match ($this->input('organizador_type')) {
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
            'eve_titulo' => ['required', 'string', 'max:255'],
            'eve_descricao' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'organizador_type' => ['required', 'string', Rule::in(['artist_profile', 'group'])],
            'organizador_id' => ['required', 'integer'],
            'eve_gratuito' => ['sometimes', 'boolean'],
            'eve_cartaz_url' => ['nullable', 'string', 'max:2048'],
            'eve_links_externos' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
