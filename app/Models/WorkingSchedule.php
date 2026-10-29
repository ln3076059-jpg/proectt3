<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingSchedule extends Model
{
    use HasFactory;

    protected $table = 'working_schedules';

    protected $fillable = [
        'doctor_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'status',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'day_of_week' => 'integer',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ON_LEAVE = 'on_leave';

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class, 'working_schedule_id');
    }

    /**
     * Tự động sinh danh sách time slots cho ca làm việc
     * @param int $durationMinutes Thời lượng mỗi ca (mặc định 30 phút)
     * @param int $capacity Số bệnh nhân tối đa mỗi khung giờ (mặc định 1)
     */
    public function generateTimeSlots(int $durationMinutes = 30, int $capacity = 1): array
    {
        $slots = [];
        $startTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->start_time);
        $endTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);

        while ($startTime->lt($endTime)) {
            $slotEnd = (clone $startTime)->addMinutes($durationMinutes);
            if ($slotEnd->gt($endTime)) {
                break;
            }

            $slot = TimeSlot::firstOrCreate(
                [
                    'doctor_id' => $this->doctor_id,
                    'date' => $this->date->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i:s'),
                ],
                [
                    'working_schedule_id' => $this->id,
                    'end_time' => $slotEnd->format('H:i:s'),
                    'capacity' => $capacity,
                    'booked_count' => 0,
                    'status' => TimeSlot::STATUS_AVAILABLE,
                    'is_active' => true,
                ]
            );

            $slots[] = $slot;
            $startTime = $slotEnd;
        }

        return $slots;
    }
}
