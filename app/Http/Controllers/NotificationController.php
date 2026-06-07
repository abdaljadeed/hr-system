<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderByRaw('read_at is null desc')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url ? redirect($url) : back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        auth()->user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notification removed.');
    }
}
