<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('appointments:release-expired', function () {
    $released = app(\App\Services\AppointmentService::class)->releaseExpiredUnpaidOnlineBookings();
    $this->info("Đã tự động giải phóng {$released} lịch hẹn VNPay quá hạn 15 phút.");
})->purpose('Giải phóng các khung giờ bị giữ bởi lịch hẹn VNPay quá 15 phút chưa thanh toán');
