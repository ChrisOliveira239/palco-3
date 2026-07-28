<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;

class GroupController extends Controller
{
    public function index()
    {
        return response()->json([
            'groups' => Group::where('gro_active', true)->paginate(),
        ]);
    }

    public function show(Group $group)
    {
        abort_if(! $group->gro_active, 404);

        return response()->json(['group' => $group]);
    }

    public function store(StoreGroupRequest $request)
    {
        $group = Group::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['group' => $group], 201);
    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        $this->authorize('update', $group);

        $group->update($request->validated());

        return response()->json(['group' => $group]);
    }

    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->update(['gro_active' => false]);

        return response()->json(['message' => 'Grupo desativado.']);
    }
}
