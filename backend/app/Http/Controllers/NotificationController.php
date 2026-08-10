<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'notifications' => $request->user()->notifications()->where('not_active', true)->latest()->paginate(),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['not_lida' => true]);

        return response()->json(['notification' => $notification]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->where('not_lida', false)->update(['not_lida' => true]);

        return response()->json(['message' => 'Todas as notificações marcadas como lidas.']);
    }

    public function destroy(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['not_active' => false]);

        return response()->json(['message' => 'Notificação removida.']);
    }
}
