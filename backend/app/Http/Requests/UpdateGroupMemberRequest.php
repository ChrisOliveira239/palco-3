<?php

namespace App\Http\Requests;

use App\Enums\Types;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupMemberRequest extends FormRequest
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
            'grm_papel' => ['required', 'string', Rule::in(Types::GROUP_MEMBER_ROLE)],
        ];
    }
}
