<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.settings', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $preferences = [];

        foreach (User::NOTIFICATION_DEFAULTS as $event => $channels) {
            foreach (array_keys($channels) as $channel) {
                $preferences[$event][$channel] = $request->boolean("notifications.{$event}.{$channel}");
            }
        }

        $request->user()->forceFill(['notification_preferences' => $preferences])->save();

        return back()->with('success', 'Notification settings saved.');
    }
}
