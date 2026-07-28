<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddGroupMemberRequest;
use App\Http\Requests\UpdateGroupMemberRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    public function index(Group $group)
    {
        return response()->json(['members' => $group->members]);
    }

    public function store(AddGroupMemberRequest $request, Group $group)
    {
        $this->authorize('manageMembers', $group);

        $group->members()->attach($request->validated('user_id'), [
            'grm_papel' => $request->validated('grm_papel') ?? 'MEMBRO',
        ]);

        return response()->json(['message' => 'Membro adicionado ao grupo.'], 201);
    }

    public function update(UpdateGroupMemberRequest $request, Group $group, User $user)
    {
        $this->authorize('manageMembers', $group);
        abort_unless($group->members()->wherePivot('user_id', $user->id)->exists(), 404);

        $group->members()->updateExistingPivot($user->id, $request->validated());

        return response()->json(['message' => 'Papel do membro atualizado.']);
    }

    public function destroy(Request $request, Group $group, User $user)
    {
        $isSelf = $request->user()->id === $user->id;

        if (! $isSelf) {
            $this->authorize('manageMembers', $group);
        }

        abort_unless($group->members()->wherePivot('user_id', $user->id)->exists(), 404);

        $group->members()->detach($user->id);

        return response()->json(['message' => 'Membro removido do grupo.']);
    }
}
