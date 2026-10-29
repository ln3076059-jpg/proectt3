<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\LabTest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();
        //
        View::composer('welcome', function ($view) {
            if (!$view->offsetExists('departments')) {
                $view->with('departments', Department::orderBy('name')->get());
            }
        });

        View::composer('layouts.header', function ($view) {
            $pendingAppointmentsCount = 0;
            if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->patient) {
                $pendingAppointmentsCount = Appointment::where('patient_id', \Illuminate\Support\Facades\Auth::user()->patient->id)
                    ->whereDate('appointment_date', '>=', \Carbon\Carbon::today())
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->count();
            }
            $view->with('pendingAppointmentsCount', $pendingAppointmentsCount);
        });

        View::composer(['home.frontend', 'layouts.profile'], function ($view) {
            $adminId = \Illuminate\Support\Facades\Cache::remember('default_chat_admin_id', 300, function () {
                return \App\Models\User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->value('id');
            });
            $view->with('defaultChatAdminId', $adminId);
        });

        View::composer('layouts.admin', function ($view) {
            $newAppointmentsCount = Appointment::where('status', 'pending')->count();
            $newLabTestsCount = LabTest::where('status', 'requested')->count();

            $view->with([
                'newAppointmentsCount' => $newAppointmentsCount,
                'newLabTestsCount' => $newLabTestsCount,
            ]);
        });
    }
}
