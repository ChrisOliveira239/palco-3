<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventMediaRequest;
use App\Http\Requests\UpdateEventMediaRequest;
use App\Models\Event;
use App\Models\EventMedia;

class EventMediaController extends Controller
{
    public function index(Event $event)
    {
        return response()->json([
            'media' => $event->media()->where('evm_active', true)->orderBy('evm_ordem')->get(),
        ]);
    }

    public function store(StoreEventMediaRequest $request, Event $event)
    {
        $this->authorize('manageMedia', $event);

        $media = $event->media()->create($request->validated());

        return response()->json(['media' => $media], 201);
    }

    public function update(UpdateEventMediaRequest $request, Event $event, EventMedia $media)
    {
        $this->authorize('manageMedia', $event);
        abort_unless($media->event_id === $event->id, 404);

        $media->update($request->validated());

        return response()->json(['media' => $media]);
    }

    public function destroy(Event $event, EventMedia $media)
    {
        $this->authorize('manageMedia', $event);
        abort_unless($media->event_id === $event->id, 404);

        $media->update(['evm_active' => false]);

        return response()->json(['message' => 'Mídia desativada.']);
    }
}
