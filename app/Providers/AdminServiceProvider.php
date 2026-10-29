<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Share admin menu data and pending appointment notification count with admin layout
        View::composer('layouts.admin', function ($view) {
            $newAppointmentsCount = 0;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('appointments')) {
                    $newAppointmentsCount = \App\Models\Appointment::where('status', 'pending')->count();
                }
            } catch (\Throwable $e) {
                $newAppointmentsCount = 0;
            }

            $view->with('newAppointmentsCount', $newAppointmentsCount);
        });
    }
}
