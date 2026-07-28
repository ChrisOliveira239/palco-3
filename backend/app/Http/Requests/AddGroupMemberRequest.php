<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddGroupMemberRequest extends FormRequest
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
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('group_members')->where(
                    fn ($query) => $query->where('group_id', $this->route('group')->id)
                ),
            ],
            'grm_papel' => ['sometimes', 'string', Rule::in(Types::GROUP_MEMBER_ROLE)],
        ];
    }
}
