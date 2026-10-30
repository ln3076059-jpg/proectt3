<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'specialization',
        'is_active',
        'license_number',
        'license_image',
        'avatar',
        'description',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Quan hệ 1 bác sĩ thuộc về 1 user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ 1 bác sĩ thuộc về 1 khoa phòng.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Quan hệ 1 bác sĩ có nhiều lịch hẹn.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Lịch làm việc của bác sĩ
     */
    public function workingSchedules()
    {
        return $this->hasMany(WorkingSchedule::class, 'doctor_id');
    }

    /**
     * Tên bác sĩ hiển thị chuẩn y khoa (tự động xử lý tiền tố học hàm/học vị, không bị lặp BS. BS.)
     */
    public function getFormattedNameAttribute(): string
    {
        $raw = trim($this->user->name ?? $this->name ?? 'Bác sĩ chuyên khoa');
        if (preg_match('/^(BS\.|BS|BSCK|ThS|TS|PGS|GS)\b/iu', $raw)) {
            return $raw;
        }
        return 'BS. ' . $raw;
    }

    /**
     * Khung giờ khám của bác sĩ
     */
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class, 'doctor_id');
    }
}
