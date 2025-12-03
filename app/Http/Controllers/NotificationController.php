<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (isset($notification->data['report_id'])) {
            return redirect()->route('reports.show', $notification->data['report_id']);
        }

        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }

    public function check()
    {
        return response()->json([
            'unread_count' => auth()->user()->unreadNotifications->count()
        ]);
    }
}
