<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $table = 'time_slots';

    protected $fillable = [
        'working_schedule_id',
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'booked_count',
        'status',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'capacity' => 'integer',
        'booked_count' => 'integer',
        'is_active' => 'boolean',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_LOCKED = 'locked';
    const STATUS_CANCELLED = 'cancelled';

    public function workingSchedule()
    {
        return $this->belongsTo(WorkingSchedule::class, 'working_schedule_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'time_slot_id');
    }

    public function activeAppointments()
    {
        return $this->hasMany(Appointment::class, 'time_slot_id')
            ->whereIn('status', Appointment::ACTIVE_SLOT_STATUSES);
    }

    /**
     * Khung giờ hiển thị (ví dụ: 08:00 - 08:30)
     */
    public function getFormattedTimeAttribute(): string
    {
        $start = substr($this->start_time, 0, 5);
        $end = substr($this->end_time, 0, 5);
        return "{$start} - {$end}";
    }

    /**
     * Thời điểm bắt đầu chính xác dạng Carbon (bao gồm ngày và giờ)
     */
    public function getStartDateTimeAttribute(): Carbon
    {
        $dateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : (string) $this->date;
        return Carbon::parse("{$dateStr} {$this->start_time}");
    }

    /**
     * Kiểm tra khung giờ có khả dụng hay không theo BR01, BR02
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active || $this->status !== self::STATUS_AVAILABLE) {
            return false;
        }

        // BR01: Không đặt lịch tại khung giờ đã qua thời điểm bắt đầu
        if ($this->start_date_time->isPast()) {
            return false;
        }

        // BR02: Còn khả năng tiếp nhận (booked_count < capacity)
        return $this->booked_count < $this->capacity;
    }

    /**
     * Số chỗ còn lại
     */
    public function getRemainingCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }
}
