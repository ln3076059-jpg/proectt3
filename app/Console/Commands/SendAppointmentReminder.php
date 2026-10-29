<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-appointment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails 1 day before confirmed appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointments = Appointment::query()
            ->whereDate('appointment_date', now()->addDay()->toDateString())
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->with(['patient', 'doctor.user', 'service'])
            ->get();

        Log::info('[SendAppointmentReminder] Found appointments for tomorrow', [
            'count' => $appointments->count(),
            'date' => now()->addDay()->toDateString(),
        ]);

        foreach ($appointments as $appointment) {
            $to = optional($appointment->patient)->email;
            Log::info('[SendAppointmentReminder] Processing appointment', [
                'appointment_id' => $appointment->id ?? null,
                'to' => $to,
            ]);

            if (! $to) {
                Log::warning('[SendAppointmentReminder] Skipped appointment due to missing patient email', [
                    'appointment_id' => $appointment->id ?? null,
                ]);
                continue;
            }

            try {
                Mail::to($to)->send(new AppointmentReminderMail($appointment));
                Log::info('[SendAppointmentReminder] Email sent successfully', [
                    'appointment_id' => $appointment->id ?? null,
                    'to' => $to,
                ]);
            } catch (\Throwable $e) {
                Log::error('[SendAppointmentReminder] Failed to send email', [
                    'appointment_id' => $appointment->id ?? null,
                    'to' => $to,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
