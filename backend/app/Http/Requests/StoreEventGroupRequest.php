<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventGroupRequest extends FormRequest
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
            'group_id' => [
                'required',
                'integer',
                'exists:groups,id',
                Rule::unique('event_group')->where(
                    fn ($query) => $query->where('event_id', $this->route('event')->id)
                ),
            ],
        ];
    }
}
