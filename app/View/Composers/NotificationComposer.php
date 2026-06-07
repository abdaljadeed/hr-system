<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check()) {
            $view->with('unreadCount', 0)->with('recentNotifications', collect());

            return;
        }

        $user = Auth::user();

        $view->with('unreadCount', $user->unreadNotifications()->count());
        $view->with('recentNotifications', $user->unreadNotifications()->latest()->take(5)->get());
    }
}
