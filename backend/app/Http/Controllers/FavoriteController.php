<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request, Event $event)
    {
        abort_unless($event->eve_status === 'PUBLICADO' && $event->eve_active, 404);

        $user = $request->user();
        abort_if($user->favoriteEvents()->where('event_id', $event->id)->exists(), 422, 'Você já favoritou esse evento.');

        $user->favoriteEvents()->attach($event->id);

        return response()->json(['message' => 'Evento favoritado.'], 201);
    }

    public function destroy(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($user->favoriteEvents()->where('event_id', $event->id)->exists(), 404);

        $user->favoriteEvents()->detach($event->id);

        return response()->json(['message' => 'Evento removido dos favoritos.']);
    }

    public function index(Request $request)
    {
        return response()->json([
            'favorites' => $request->user()->favoriteEvents()->get(),
        ]);
    }
}
