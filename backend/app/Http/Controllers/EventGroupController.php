<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventGroupRequest;
use App\Http\Requests\UpdateEventGroupRequest;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\Request;

class EventGroupController extends Controller
{
    public function index(Event $event)
    {
        return response()->json([
            'groups' => $event->groups()->wherePivot('evg_status', 'ACEITO')->get(),
        ]);
    }

    public function store(StoreEventGroupRequest $request, Event $event)
    {
        $this->authorize('manageParticipants', $event);

        $group = Group::findOrFail($request->validated('group_id'));
        $status = ($request->user()->isGroupOwner($group) || $request->user()->isGroupAdmin($group))
            ? 'ACEITO'
            : 'PENDENTE';

        $event->groups()->attach($group->id, ['evg_status' => $status]);

        return response()->json(['message' => 'Grupo convidado para o evento.'], 201);
    }

    public function update(UpdateEventGroupRequest $request, Event $event, Group $group)
    {
        abort_unless($request->user()->isGroupOwner($group) || $request->user()->isGroupAdmin($group), 403);
        abort_unless($event->groups()->wherePivot('group_id', $group->id)->exists(), 404);

        $event->groups()->updateExistingPivot($group->id, $request->validated());

        return response()->json(['message' => 'Convite atualizado.']);
    }

    public function destroy(Request $request, Event $event, Group $group)
    {
        $isSelf = $request->user()->isGroupOwner($group) || $request->user()->isGroupAdmin($group);

        if (! $isSelf) {
            $this->authorize('manageParticipants', $event);
        }

        abort_unless($event->groups()->wherePivot('group_id', $group->id)->exists(), 404);

        $event->groups()->detach($group->id);

        return response()->json(['message' => 'Grupo removido do evento.']);
    }
}
