<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    /**
     * Bệnh nhân chỉ xem được lịch của chính mình.
     * Bác sĩ chỉ xem được lịch được phân công cho mình.
     * Admin xem được tất cả.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $user->doctor && (int) $appointment->doctor_id === (int) $user->doctor->id;
        }

        if ($user->patient) {
            return (int) $appointment->patient_id === (int) $user->patient->id;
        }

        return false;
    }

    /**
     * Quyền cập nhật trạng thái lịch hẹn
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $user->doctor && (int) $appointment->doctor_id === (int) $user->doctor->id;
        }

        if ($user->patient) {
            return (int) $appointment->patient_id === (int) $user->patient->id;
        }

        return false;
    }

    /**
     * Quyền dời lịch hẹn
     */
    public function reschedule(User $user, Appointment $appointment): bool
    {
        return $this->cancel($user, $appointment);
    }

    /**
     * Quyền hủy lịch hẹn
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $user->doctor && (int) $appointment->doctor_id === (int) $user->doctor->id;
        }

        if ($user->patient) {
            return (int) $appointment->patient_id === (int) $user->patient->id;
        }

        return false;
    }

    /**
     * Quyền xóa lịch hẹn (chỉ Admin)
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin');
    }
}
