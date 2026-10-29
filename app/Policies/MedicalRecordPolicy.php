<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicalRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Quyền xem hồ sơ bệnh án:
     * - Admin xem được tất cả
     * - Bác sĩ xem được nếu là người khám hoặc được phân công cho ca khám này
     * - Bệnh nhân chỉ xem được hồ sơ của chính mình
     */
    public function view(User $user, MedicalRecord $record): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor') && $user->doctor) {
            $appointment = $record->appointment;
            if ($appointment && (int) $appointment->doctor_id === (int) $user->doctor->id) {
                return true;
            }
        }

        if ($user->patient) {
            return (int) $record->patient_id === (int) $user->patient->id;
        }

        return false;
    }

    /**
     * Quyền cập nhật hồ sơ bệnh án:
     * - Chỉ bác sĩ phụ trách ca khám hoặc Admin
     */
    public function update(User $user, MedicalRecord $record): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor') && $user->doctor) {
            $appointment = $record->appointment;
            return $appointment && (int) $appointment->doctor_id === (int) $user->doctor->id;
        }

        return false;
    }

    /**
     * Quyền xóa hồ sơ: chỉ Admin
     */
    public function delete(User $user, MedicalRecord $record): bool
    {
        return $user->hasRole('admin');
    }
}
