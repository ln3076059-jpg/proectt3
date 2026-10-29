<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Để gửi email nhắc lịch trước 1 ngày, cách chuẩn nhất trong Laravel là:
     * Tạo Mailable cho nội dung email.
     * Tạo Console Command chạy mỗi ngày, quét các lịch hẹn ngày mai và gửi mail.
     * Đăng ký command trong Kernel để Laravel Scheduler gọi tự động.
     * nếu muốn chạy thự liền thì dùng $schedule->command('app:send-appointment-reminder')->everyMinute(); và chạy lệnh php artisan schedule:run.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Gửi nhắc lịch mỗi ngày lúc 07:00
        $schedule->command('app:send-appointment-reminder')->dailyAt('07:00');
        // $schedule->command('app:send-appointment-reminder')->everyMinute();

        // Tự động giải phóng các lịch hẹn VNPay quá 15 phút & hủy lịch hẹn quá 24 giờ chưa thanh toán
        $schedule->command('app:auto-cancel-unpaid-appointments')->everyMinute();

        // Tự động duyệt các lịch hẹn đã thanh toán trong vòng 24 giờ - chạy mỗi giờ
        $schedule->command('app:auto-approve-paid-appointments')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
