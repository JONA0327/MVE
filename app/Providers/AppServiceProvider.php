<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\VucemStatusService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Gate: Admin (Acceso al panel general)
        // Permite entrar si es Super Admin O Admin
        Gate::define('admin', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        // Gate: Super Admin (Para cosas exclusivas como configs globales)
        Gate::define('super_admin', function (User $user) {
            return $user->role === 'super_admin';
        });

        View::composer('layouts.app', function ($view) {
            $service = new VucemStatusService();
            $view->with('isVucemDown', $service->isDown());
        });
    }
}