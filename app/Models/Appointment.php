<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_code',
        'idempotency_key',
        'patient_id',
        'doctor_id',
        'service_id',
        'time_slot_id',
        'appointment_date',
        'status',
        'medical_examination',
        'note',
        'payment_status',
        'payment_reference',
        'transaction_id',
        'transaction_ref',
        'payment_method',
        'total_amount',
        'total',
        'paid_at',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'refund_status',
        'refund_amount',
        'refund_note',
        'booking_for',
        'relative_name',
        'relative_dob',
        'relative_gender',
        'relative_relationship',
        'checked_in_at',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'relative_dob' => 'date',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_NO_SHOW = 'no_show';

    // Danh sách các trạng thái tính là đang chiếm giữ chỗ trong khung giờ khám (TimeSlot)
    const ACTIVE_SLOT_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CHECKED_IN,
        self::STATUS_COMPLETED,
    ];

    // Payment status constants
    const PAYMENT_SUCCESS = 'success';
    const PAYMENT_FAILED = 'failed';

    // Refund status constants
    const REFUND_NONE = 'none';
    const REFUND_PENDING = 'pending';
    const REFUND_REFUNDED = 'refunded';
    const REFUND_REJECTED = 'rejected';

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(AppointmentStatusHistory::class, 'appointment_id')->orderBy('created_at', 'asc');
    }

    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    /**
     * Nhãn tiếng Việt cho trạng thái lịch hẹn
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_CHECKED_IN => 'Đã tiếp đón',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_REJECTED => 'Bị từ chối',
            self::STATUS_NO_SHOW => 'Vắng mặt (Bỏ hẹn)',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge CSS class cho trạng thái
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_CONFIRMED => 'bg-primary text-white',
            self::STATUS_CHECKED_IN => 'bg-info text-dark',
            self::STATUS_COMPLETED => 'bg-success text-white',
            self::STATUS_CANCELLED => 'bg-secondary text-white',
            self::STATUS_REJECTED => 'bg-danger text-white',
            self::STATUS_NO_SHOW => 'bg-dark text-white',
            default => 'bg-info text-dark',
        };
    }

    /**
     * Chuẩn hóa tên người khám (loại bỏ danh xưng Bé/Cháu/Anh/Chị/Ông/Bà và khoảng trắng thừa)
     * để so khớp chính xác tiền sử bệnh án & chống đặt trùng lịch cho cùng một người thân.
     */
    public static function normalizePersonName(?string $name): string
    {
        $clean = mb_strtolower(trim((string) $name), 'UTF-8');
        $clean = preg_replace('/\s+/u', ' ', $clean);
        $clean = preg_replace('/^(bé\s+trai|bé\s+gái|bé|cháu|anh|chị|ông|bà|cụ|cô|chú|bác|em)\s+/iu', '', $clean);
        return trim((string) $clean);
    }

    /**
     * Tính bảng kê viện phí đa tầng (Phí khám ban đầu + Xét nghiệm CLS + Đơn thuốc EMR + Đồng chi trả BHYT + Đối soát số dư đã thu)
     */
    public function calculateCostBreakdown(): array
    {
        $consultationFee = (float) (optional($this->service)->price ?? $this->total_amount ?? $this->total ?? 0);

        $prescriptionFee = 0.0;
        $labTestFee = 0.0;
        $labTestCount = 0;

        if ($this->medicalRecord) {
            foreach ($this->medicalRecord->prescriptionItems ?? [] as $item) {
                $unitPrice = (float) (optional($item->medicine)->price ?? 0);
                $prescriptionFee += $unitPrice * max(1, (int) $item->quantity);
            }

            $labTests = $this->medicalRecord->labTests ?? collect();
            $labTestCount = $labTests->count();
            foreach ($labTests as $lt) {
                $typePrice = (float) (optional($lt->test_type)->price ?? 150000);
                $labTestFee += $typePrice > 0 ? $typePrice : 150000;
            }
        }

        $grossTotal = $consultationFee + $labTestFee + $prescriptionFee;

        // Kiểm tra hưởng BHYT (áp dụng khi khám bản thân và có mã thẻ BHYT 10 hoặc 15 ký tự)
        $rawBhyt = preg_replace('/[\s\-]+/', '', (string) optional($this->patient)->insurance_number);
        $hasValidBhyt = ($this->booking_for !== 'relative') && in_array(strlen($rawBhyt), [10, 15], true);
        $bhytRate = $hasValidBhyt ? 0.80 : 0.0; // Mức hưởng BHYT đúng tuyến tiêu chuẩn 80%

        // BHYT chi trả trên phí khám và cận lâm sàng/thuốc trong danh mục
        $bhytCoveredAmount = $hasValidBhyt ? round($grossTotal * $bhytRate, 0) : 0.0;
        $patientCopayAmount = max(0.0, $grossTotal - $bhytCoveredAmount);

        $isPaidInitial = $this->payment_status === self::PAYMENT_SUCCESS;
        $paidAmount = $isPaidInitial
            ? (float) ($this->total_amount ?? $this->total ?? $consultationFee)
            : 0.0;

        $payableTarget = $hasValidBhyt ? $patientCopayAmount : $grossTotal;
        $balanceDue = max(0.0, $payableTarget - $paidAmount);

        if (!$isPaidInitial) {
            $settlementStatus = 'unpaid';
            $settlementLabel = 'Chưa thanh toán';
        } elseif ($balanceDue > 0 && ($labTestFee + $prescriptionFee) > 0) {
            $settlementStatus = 'partially_paid';
            $settlementLabel = 'Đã thu phí khám — Chờ tất toán CLS/Thuốc';
        } else {
            $settlementStatus = 'fully_paid';
            $settlementLabel = 'Đã tất toán toàn bộ';
        }

        return [
            'consultation_fee' => $consultationFee,
            'lab_test_count' => $labTestCount,
            'lab_test_fee' => $labTestFee,
            'prescription_fee' => $prescriptionFee,
            'gross_total' => $grossTotal,
            'has_bhyt' => $hasValidBhyt,
            'bhyt_number' => $hasValidBhyt ? strtoupper($rawBhyt) : null,
            'bhyt_rate_percent' => (int) ($bhytRate * 100),
            'bhyt_covered_amount' => $bhytCoveredAmount,
            'patient_copay_amount' => $patientCopayAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'settlement_status' => $settlementStatus,
            'settlement_label' => $settlementLabel,
        ];
    }

    /**
     * Tên bệnh nhân hiển thị (Hỗ trợ đặt cho bản thân hoặc người thân)
     */
    public function getPatientDisplayNameAttribute(): string
    {
        if ($this->booking_for === 'relative' && !empty($this->relative_name)) {
            $rel = $this->relative_relationship ? " ({$this->relative_relationship})" : '';
            return $this->relative_name . $rel;
        }
        return $this->patient->user->name ?? $this->patient->name ?? 'Bệnh nhân';
    }

    /**
     * Nhãn tiếng Việt cho trạng thái thanh toán
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_SUCCESS => 'Đã thanh toán',
            self::PAYMENT_FAILED => 'Chưa thanh toán',
            default => 'Chưa thanh toán',
        };
    }

    /**
     * Sinh mã lịch hẹn duy nhất: TT-YYYYMMDD-ID hoặc TT-YYYYMMDD-RAND
     */
    public static function generateCode(?int $id = null): string
    {
        $suffix = $id !== null ? str_pad((string)$id, 4, '0', STR_PAD_LEFT) : strtoupper(bin2hex(random_bytes(2)));
        return 'TT-' . now()->format('Ymd') . '-' . $suffix;
    }

    /**
     * Kiểm tra xem lịch hẹn có thể hủy được hay không theo BR05, BR06
     */
    public function canBeCancelledByPatient(int $deadlineHours = 2): array
    {
        // 1. Chỉ cho phép hủy khi lịch hẹn đang pending hoặc confirmed
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])) {
            return [
                'can' => false,
                'reason' => 'Chỉ có thể hủy lịch hẹn đang ở trạng thái Chờ xác nhận hoặc Đã xác nhận.',
            ];
        }

        // 2. Xác định thời điểm khám
        $appointmentTime = null;
        if ($this->timeSlot) {
            $appointmentTime = $this->timeSlot->start_date_time;
        } elseif ($this->appointment_date) {
            $date = Carbon::parse($this->appointment_date);
            if ($this->medical_examination && strpos($this->medical_examination, 'chiều') !== false) {
                $date->setTime(13, 0, 0);
            } else {
                $date->setTime(7, 30, 0);
            }
            $appointmentTime = $date;
        }

        if ($appointmentTime && $appointmentTime->isPast()) {
            return [
                'can' => false,
                'reason' => 'Không thể hủy lịch khám đã qua thời gian tiếp nhận.',
            ];
        }

        if ($appointmentTime) {
            $hoursLeft = now()->diffInHours($appointmentTime, false);
            if ($hoursLeft < $deadlineHours) {
                return [
                    'can' => false,
                    'reason' => "Lịch khám chỉ được phép hủy trước giờ khám tối thiểu {$deadlineHours} tiếng. Hiện chỉ còn " . max(0, round(now()->diffInMinutes($appointmentTime, false) / 60, 1)) . " tiếng.",
                ];
            }
        }

        return ['can' => true, 'reason' => ''];
    }

    /**
     * Kiểm tra xem lịch hẹn có thể dời lịch được hay không
     */
    public function canBeRescheduledByPatient(int $deadlineHours = 2): array
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])) {
            return [
                'can' => false,
                'reason' => 'Chỉ có thể dời lịch hẹn đang ở trạng thái Chờ xác nhận hoặc Đã xác nhận.',
            ];
        }

        $appointmentTime = $this->timeSlot ? $this->timeSlot->start_date_time : Carbon::parse($this->appointment_date);
        if ($appointmentTime && $appointmentTime->isPast()) {
            return [
                'can' => false,
                'reason' => 'Không thể dời lịch khám đã qua thời gian tiếp nhận.',
            ];
        }

        if ($appointmentTime) {
            $hoursLeft = now()->diffInHours($appointmentTime, false);
            if ($hoursLeft < $deadlineHours) {
                return [
                    'can' => false,
                    'reason' => "Lịch khám chỉ được phép dời trước giờ khám tối thiểu {$deadlineHours} tiếng.",
                ];
            }
        }

        return ['can' => true, 'reason' => ''];
    }
}

