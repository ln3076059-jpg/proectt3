<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\TimeSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCancelUnpaidAppointments extends Command
{
    protected $signature = 'app:auto-cancel-unpaid-appointments';

    protected $description = 'Tự động hủy các lịch hẹn chờ duyệt quá 24 giờ chưa thanh toán và giải phóng khung giờ';

    public function handle(): int
    {
        // 1. Giải phóng ngay các lịch hẹn thanh toán trực tuyến VNPay quá hạn 15 phút chưa hoàn tất
        $releasedOnline = app(\App\Services\AppointmentService::class)->releaseExpiredUnpaidOnlineBookings();

        // 2. Giải phóng các lịch hẹn chờ duyệt quá 24 giờ chưa thanh toán
        $cutoff = now()->subHours(24);

        $appointments = Appointment::where('status', Appointment::STATUS_PENDING)
            ->where('payment_status', '!=', Appointment::PAYMENT_SUCCESS)
            ->where('created_at', '<=', $cutoff)
            ->get();

        $cancelledCount = $releasedOnline;

        foreach ($appointments as $appointment) {
            try {
                DB::transaction(function () use ($appointment, &$cancelledCount) {
                    $locked = Appointment::where('id', $appointment->id)->lockForUpdate()->first();
                    if (!$locked || $locked->status !== Appointment::STATUS_PENDING) {
                        return;
                    }

                    if ($locked->time_slot_id) {
                        $slot = TimeSlot::where('id', $locked->time_slot_id)->lockForUpdate()->first();
                        if ($slot) {
                            $slot->booked_count = max(0, $slot->booked_count - 1);
                            if ($slot->status === TimeSlot::STATUS_LOCKED && $slot->booked_count < $slot->capacity) {
                                $slot->status = TimeSlot::STATUS_AVAILABLE;
                            }
                            $slot->save();
                        }
                    }

                    $oldStatus = $locked->status;
                    $locked->status = Appointment::STATUS_CANCELLED;
                    $locked->cancellation_reason = 'Tự động hủy do quá 24 giờ chưa xác nhận/thanh toán';
                    $locked->cancelled_at = now();
                    $locked->save();

                    AppointmentStatusHistory::create([
                        'appointment_id' => $locked->id,
                        'from_status' => $oldStatus,
                        'to_status' => Appointment::STATUS_CANCELLED,
                        'changed_by_user_id' => null,
                        'action_role' => 'system',
                        'note' => 'Hệ thống tự động hủy sau 24 giờ chưa xác nhận/thanh toán',
                    ]);

                    $cancelledCount++;
                });
            } catch (\Throwable $e) {
                Log::error('[AutoCancelUnpaidAppointments] Error cancelling appointment #' . $appointment->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Đã tự động hủy {$cancelledCount} lịch hẹn quá hạn.");
        return self::SUCCESS;
    }
}
