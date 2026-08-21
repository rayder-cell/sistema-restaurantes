<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Restaurante;


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
        // Compartir primer restaurante con el layout superadmin
        View::composer('layouts.superadmin', function ($view) {
            $view->with(
                'primerRestaurante',
                Restaurante::where('estado', 'activo')
                    ->orderBy('created_at')
                    ->first()
            );
        });
    }
}
