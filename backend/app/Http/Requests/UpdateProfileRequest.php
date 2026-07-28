<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'use_name' => ['sometimes', 'string', 'max:255'],
            'use_bio' => ['sometimes', 'nullable', 'string'],
            'use_avatar_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'use_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'use_state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'use_latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'use_longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
