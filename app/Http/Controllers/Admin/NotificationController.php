<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.notifications.index', [
            'notifications' => $request->user()->notifications()->paginate(25),
        ]);
    }

    public function open(Request $request, string $notification)
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('admin.notifications.index'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function clear(Request $request)
    {
        $request->user()->notifications()->delete();

        return back()->with('success', 'Notifications cleared.');
    }
}
