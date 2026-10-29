<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment->loadMissing(['patient', 'doctor.user', 'service']);
    }

    public function build()
    {
        return $this->subject('Lịch hẹn của bạn đã được xác nhận')
            ->view('emails.appointment_approved');
    }
}
