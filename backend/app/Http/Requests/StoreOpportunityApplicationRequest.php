<?php

namespace App\Http\Requests;

use App\Models\ArtistProfile;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $opportunity = $this->route('opportunity');
        $user = $this->user();

        if ($opportunity->criador_type === ArtistProfile::class) {
            return $user->artistProfile?->id !== $opportunity->criador_id;
        }

        if ($opportunity->criador_type === Group::class) {
            return ! ($user->isGroupOwner($opportunity->criador) || $user->isGroupAdmin($opportunity->criador));
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'opa_mensagem' => ['nullable', 'string'],
        ];
    }
}
