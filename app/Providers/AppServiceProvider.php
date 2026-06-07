<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\NotificationComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::before(function (User $user) {
            return $user->hasRole('Admin') ? true : null;
        });

        View::composer('layouts.topbar', NotificationComposer::class);
    }
}
