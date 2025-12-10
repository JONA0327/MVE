<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate; // <--- AGREGAR ESTO
use App\Models\User; // <--- AGREGAR ESTO
use App\Services\VucemStatusService;

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
        // 1. Definir el Gate 'admin' para proteger las rutas
        Gate::define('admin', function (User $user) {
            return $user->is_admin; // Retorna true si es admin, false si no
        });

        // 2. Compartir el estado de VUCEM con el layout principal
        View::composer('layouts.app', function ($view) {
            $service = new VucemStatusService();
            $view->with('isVucemDown', $service->isDown());
        });
    }
}