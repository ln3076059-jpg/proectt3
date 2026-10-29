<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoApprovePaidAppointments extends Command
{
    protected $signature = 'app:auto-approve-paid-appointments';

    protected $description = 'Tự động xác nhận các lịch hẹn đang chờ duyệt nhưng đã thanh toán thành công';

    public function handle(): int
    {
        $appointments = Appointment::where('status', Appointment::STATUS_PENDING)
            ->where('payment_status', Appointment::PAYMENT_SUCCESS)
            ->get();

        $approvedCount = 0;

        foreach ($appointments as $appointment) {
            try {
                DB::transaction(function () use ($appointment, &$approvedCount) {
                    $locked = Appointment::where('id', $appointment->id)->lockForUpdate()->first();
                    if (!$locked || $locked->status !== Appointment::STATUS_PENDING) {
                        return;
                    }

                    $oldStatus = $locked->status;
                    $locked->status = Appointment::STATUS_CONFIRMED;
                    $locked->save();

                    AppointmentStatusHistory::create([
                        'appointment_id' => $locked->id,
                        'from_status' => $oldStatus,
                        'to_status' => Appointment::STATUS_CONFIRMED,
                        'changed_by_user_id' => null,
                        'action_role' => 'system',
                        'note' => 'Hệ thống tự động duyệt lịch hẹn đã thanh toán',
                    ]);

                    $approvedCount++;
                });
            } catch (\Throwable $e) {
                Log::error('[AutoApprovePaidAppointments] Error approving appointment #' . $appointment->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Đã tự động duyệt {$approvedCount} lịch hẹn đã thanh toán.");
        return self::SUCCESS;
    }
}
