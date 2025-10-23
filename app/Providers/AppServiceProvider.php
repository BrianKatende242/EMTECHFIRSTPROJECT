<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Doctor;
use App\Models\School;
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

        // Share $school with all views when available (session-based auth)
        View::composer('*', function ($view) {
            // If a controller/view already set a school variable, don't overwrite it
            $existing = $view->getData()['school'] ?? null;
            if ($existing) {
                return;
            }

            $school = null;

            // Check session for authenticated user
            $authenticatedUser = session('authenticated_user');
            if ($authenticatedUser && $authenticatedUser['type'] === 'school') {
                $school = School::find($authenticatedUser['id']);
            }

            if ($school) {
                $view->with('school', $school);
            }
        });

        // Register helper function to get authenticated school
        // Note: This function is now defined in app/Helpers/SchoolHelper.php and autoloaded
    }
}
