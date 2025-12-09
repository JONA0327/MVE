<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Importante
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Definimos la puerta 'admin'
        // Simplemente verifica si el usuario tiene la bandera is_admin en true
        Gate::define('admin', function (User $user) {
            return $user->is_admin;
        });
    }
}