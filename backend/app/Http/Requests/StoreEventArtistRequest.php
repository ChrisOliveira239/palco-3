<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventArtistRequest extends FormRequest
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
            'artist_profile_id' => [
                'required',
                'integer',
                'exists:artist_profiles,id',
                Rule::unique('event_artist')->where(
                    fn ($query) => $query->where('event_id', $this->route('event')->id)
                ),
            ],
        ];
    }
}
