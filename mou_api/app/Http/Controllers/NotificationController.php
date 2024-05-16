<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing notifications of the resource.
     */
    public function list(Request $request)
    {
        $data = $request->only(['type', 'limit']);
        $user = auth()->user()->user;
        $notifications = $user->notifications()
            ->where('data->user_type', $data['type']);
        if (isset($data['limit'])) {
            $notifications = $notifications->paginate($data['limit']);
        } else {
            $notifications = $notifications->get();
        }
        $user->unreadNotifications()->where('data->user_type', $data['type'])->update(['read_at' => now()]);

        return NotificationResource::collection($notifications);
    }

    /**
     * Count a list unread notifications.
     */
    public function count(Request $request)
    {
        $user = auth()->user()->user;

        return response()->json([
            'total' => $user->unreadNotifications()->where('data->user_type', $request->type)->count(),
        ], 200);
    }
}
