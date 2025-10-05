<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @param UrlGenerator $url
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        // Use Bootstrap markup for pagination links
        Paginator::useBootstrap();

        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }

        // Share $doctor with all views when available (authenticated guard or route param)
        View::composer('*', function ($view) {
            // If a controller/view already set a doctor variable, don't overwrite it
            $existing = $view->getData()['doctor'] ?? null;
            if ($existing) {
                return;
            }

            $doctor = null;

            // If doctor guard is authenticated
            if (Auth::guard('doctor')->check()) {
                $doctor = Auth::guard('doctor')->user();
            }

            // If route has doctorId param, try to load doctor
            $route = Route::current();
            if (!$doctor && $route) {
                $doctorId = $route->parameter('doctorId') ?? $route->parameter('doctor');
                if ($doctorId) {
                    $doctor = Doctor::find($doctorId);
                }
            }

            if ($doctor) {
                $view->with('doctor', $doctor);
            }
        });
    }
}
