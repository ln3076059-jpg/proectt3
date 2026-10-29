<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\WorkingSchedule;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppointmentWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $patientUser;
    protected Patient $patient;
    protected User $adminUser;
    protected Doctor $doctor;
    protected Service $service;
    protected TimeSlot $timeSlot;
    protected AppointmentService $appointmentService;

    protected function setUp(): void
    {
        parent::setUp();

        if (Doctor::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed');
        }

        $this->appointmentService = app(AppointmentService::class);

        $this->patient = Patient::first();
        $this->patientUser = $this->patient->user;

        $this->adminUser = User::where('email', 'admin@thaithinh.vn')->first();
        $this->doctor = Doctor::where('is_active', true)->first();
        $this->service = Service::first();

        // Tạo khung giờ kiểm thử ngày tương lai riêng biệt (20 ngày sau)
        $testDate = Carbon::today()->addDays(20)->toDateString();
        $schedule = WorkingSchedule::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'date' => $testDate],
            [
                'day_of_week' => Carbon::parse($testDate)->dayOfWeek,
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'status' => WorkingSchedule::STATUS_ACTIVE,
            ]
        );

        $this->timeSlot = TimeSlot::firstOrCreate(
            [
                'doctor_id' => $this->doctor->id,
                'date' => $testDate,
                'start_time' => '09:00:00',
            ],
            [
                'working_schedule_id' => $schedule->id,
                'end_time' => '09:30:00',
                'capacity' => 2,
                'booked_count' => 0,
                'status' => TimeSlot::STATUS_AVAILABLE,
                'is_active' => true,
            ]
        );
    }

    /**
     * Test 1: Bệnh nhân đặt lịch thành công với TimeSlot hợp lệ, booked_count tăng 1
     */
    public function test_patient_can_book_appointment_successfully(): void
    {
        $initialBooked = $this->timeSlot->booked_count;

        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'note' => 'Khám sức khỏe kiểm tra định kỳ',
        ], $this->patientUser);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertEquals(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertEquals($this->patient->id, $appointment->patient_id);
        $this->assertEquals($this->timeSlot->id, $appointment->time_slot_id);

        $this->timeSlot->refresh();
        $this->assertEquals($initialBooked + 1, $this->timeSlot->booked_count);
    }

    /**
     * Test 2: Bệnh nhân hủy lịch thành công, booked_count được phục hồi ngay lập tức
     */
    public function test_cancelling_appointment_restores_timeslot_capacity(): void
    {
        // 1. Đặt lịch trước
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'note' => 'Lịch hẹn sắp hủy',
        ], $this->patientUser);

        $this->timeSlot->refresh();
        $this->assertEquals(1, $this->timeSlot->booked_count);

        // 2. Hủy lịch hẹn
        $cancelledAppointment = $this->appointmentService->cancelAppointment(
            $appointment,
            $this->patientUser,
            'Có việc đột xuất không thể đến'
        );

        $this->assertEquals(Appointment::STATUS_CANCELLED, $cancelledAppointment->status);
        $this->assertNotNull($cancelledAppointment->cancelled_at);

        // 3. Kiểm tra booked_count của TimeSlot đã được hoàn lại về 0
        $this->timeSlot->refresh();
        $this->assertEquals(0, $this->timeSlot->booked_count);
        $this->assertEquals(TimeSlot::STATUS_AVAILABLE, $this->timeSlot->status);
    }

    /**
     * Test 3: Không thể hủy lịch hẹn đã hoàn thành (completed)
     */
    public function test_cannot_cancel_completed_appointment(): void
    {
        $completedAppointment = Appointment::create([
            'appointment_code' => Appointment::generateCode(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->subDay(),
            'medical_examination' => 'Ca sáng (07:30 - 11:30)',
            'status' => Appointment::STATUS_COMPLETED,
            'payment_status' => Appointment::PAYMENT_SUCCESS,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->appointmentService->cancelAppointment(
            $completedAppointment,
            $this->patientUser,
            'Muốn hủy lịch đã khám'
        );
    }

    /**
     * Test 4: Không thể đặt lịch với khung giờ trong quá khứ
     */
    public function test_cannot_book_past_timeslot(): void
    {
        $pastDate = now()->subDays(5)->toDateString();
        $pastSchedule = WorkingSchedule::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'date' => $pastDate],
            [
                'day_of_week' => 1,
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
                'status' => WorkingSchedule::STATUS_ACTIVE,
            ]
        );

        $pastSlot = TimeSlot::firstOrCreate(
            [
                'doctor_id' => $this->doctor->id,
                'date' => $pastDate,
                'start_time' => '08:00:00',
            ],
            [
                'working_schedule_id' => $pastSchedule->id,
                'end_time' => '08:30:00',
                'capacity' => 1,
                'booked_count' => 0,
                'status' => TimeSlot::STATUS_AVAILABLE,
                'is_active' => true,
            ]
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $pastSlot->date->toDateString(),
            'time_slot_id' => $pastSlot->id,
            'note' => 'Cố tình đặt lịch quá khứ',
        ], $this->patientUser);
    }

    /**
     * Test 5: Bệnh nhân đặt lịch hộ cho người thân (Con nhỏ / Bố mẹ)
     */
    public function test_patient_can_book_appointment_for_relative(): void
    {
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'booking_for' => 'relative',
            'relative_name' => 'Nguyễn Minh Khang',
            'relative_relationship' => 'Con nhỏ',
            'relative_dob' => '2022-05-15',
            'relative_gender' => 'male',
            'note' => 'Bé bị ho sốt 2 ngày',
        ], $this->patientUser);

        $this->assertEquals('relative', $appointment->booking_for);
        $this->assertEquals('Nguyễn Minh Khang', $appointment->relative_name);
        $this->assertEquals('Con nhỏ', $appointment->relative_relationship);
        $this->assertEquals('Nguyễn Minh Khang (Con nhỏ)', $appointment->patient_display_name);
    }

    /**
     * Test 6: Bệnh nhân dời lịch khám sang khung giờ mới thành công (Reschedule)
     */
    public function test_patient_can_reschedule_appointment_successfully(): void
    {
        // Tạo slot B ngày tiếp theo
        $testDateB = Carbon::today()->addDays(21)->toDateString();
        $scheduleB = WorkingSchedule::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'date' => $testDateB],
            [
                'day_of_week' => Carbon::parse($testDateB)->dayOfWeek,
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'status' => WorkingSchedule::STATUS_ACTIVE,
            ]
        );

        $slotB = TimeSlot::firstOrCreate(
            [
                'doctor_id' => $this->doctor->id,
                'date' => $testDateB,
                'start_time' => '10:00:00',
            ],
            [
                'working_schedule_id' => $scheduleB->id,
                'end_time' => '10:30:00',
                'capacity' => 2,
                'booked_count' => 0,
                'status' => TimeSlot::STATUS_AVAILABLE,
                'is_active' => true,
            ]
        );

        // 1. Đặt lịch ở Slot A
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'note' => 'Lịch trước khi dời',
        ], $this->patientUser);

        $this->timeSlot->refresh();
        $this->assertEquals(1, $this->timeSlot->booked_count);
        $this->assertEquals(0, $slotB->booked_count);

        // 2. Dời sang Slot B
        $rescheduled = $this->appointmentService->rescheduleAppointment(
            $appointment,
            $slotB->id,
            $this->patientUser,
            'Bận việc đột xuất'
        );

        // 3. Kiểm tra bảo toàn capacity
        $this->timeSlot->refresh();
        $slotB->refresh();

        $this->assertEquals(0, $this->timeSlot->booked_count, 'Slot A phải được nhả chỗ');
        $this->assertEquals(1, $slotB->booked_count, 'Slot B phải được tăng chỗ');
        $this->assertEquals($slotB->id, $rescheduled->time_slot_id);
    }

    /**
     * Test 7: Nhân viên / Bác sĩ tiếp đón bệnh nhân có mặt tại phòng chờ (Check-in)
     */
    public function test_doctor_or_admin_can_check_in_patient(): void
    {
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
        ], $this->patientUser);

        $checkedInAppt = $this->appointmentService->updateStatus(
            $appointment,
            Appointment::STATUS_CHECKED_IN,
            $this->adminUser,
            'Bệnh nhân có mặt tại quầy tiếp đón 125 Thái Thịnh'
        );

        $this->assertEquals(Appointment::STATUS_CHECKED_IN, $checkedInAppt->status);
        $this->assertNotNull($checkedInAppt->checked_in_at);
        $this->assertEquals('Đã tiếp đón', $checkedInAppt->status_label);
    }

    /**
     * Test 8: Quản trị viên xác nhận hoàn tiền viện phí (refunded) cho lịch hẹn đã thu tiền bị hủy
     */
    public function test_admin_can_confirm_refund_for_cancelled_paid_appointment(): void
    {
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'payment_status' => Appointment::PAYMENT_SUCCESS,
        ], $this->patientUser);

        // Hủy lịch hẹn đã thanh toán -> refund_status tự động chuyển sang pending
        $cancelledAppt = $this->appointmentService->cancelAppointment(
            $appointment,
            $this->patientUser,
            'Bận công tác đột xuất'
        );
        $this->assertEquals(Appointment::REFUND_PENDING, $cancelledAppt->refund_status);

        // Admin xác nhận hoàn tiền -> refund_status chuyển sang refunded
        $refundedAppt = $this->appointmentService->confirmRefund(
            $cancelledAppt,
            $this->adminUser,
            'Đã chuyển khoản hoàn tiền viện phí 100% cho bệnh nhân'
        );

        $this->assertEquals(Appointment::REFUND_REFUNDED, $refundedAppt->refund_status);
        $this->assertEquals((float) $this->service->price, (float) $refundedAppt->refund_amount);
        $this->assertDatabaseHas('appointment_status_histories', [
            'appointment_id' => $refundedAppt->id,
            'changed_by_user_id' => $this->adminUser->id,
        ]);
    }

    /**
     * Test 9: Bác sĩ lưu bệnh án qua DoctorRecordController đồng bộ trừ kho qua AppointmentService và tách biệt tiền sử người thân
     */
    public function test_doctor_record_update_deducts_stock_and_separates_relative_history(): void
    {
        $medicine = \App\Models\Medicine::firstOrCreate(
            ['name' => 'Amoxicillin 500mg Test'],
            ['slug' => 'amoxicillin-500mg-test', 'unit' => 'Viên', 'stock' => 50, 'price' => 2500]
        );
        $initialStock = (int) $medicine->stock;

        // 1. Tạo lịch khám cho chính chủ tài khoản (self) và lưu chẩn đoán
        $selfAppt = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'booking_for' => 'self',
        ], $this->patientUser);

        $selfRecord = \App\Models\MedicalRecord::create([
            'appointment_id' => $selfAppt->id,
            'patient_id' => $this->patient->id,
            'diagnosis' => 'Viêm họng cấp ở người lớn (Bản thân)',
            'description' => 'Đau rát họng',
        ]);

        // Cập nhật đơn thuốc và chuyển completed qua DoctorRecordController
        $doctorUser = $this->doctor->user;
        $this->actingAs($doctorUser, 'web_doctor')
            ->put(route('doctor.records.update', $selfRecord->id), [
                'diagnosis' => 'Viêm họng cấp đã kê đơn',
                'description' => 'Đau họng nhẹ',
                'doctor_conclusion' => 'Uống thuốc đủ 5 ngày',
                'status' => Appointment::STATUS_COMPLETED,
                'prescription_items' => [
                    [
                        'medicine_id' => $medicine->id,
                        'quantity' => 10,
                        'unit' => 'Viên',
                        'usage' => 'Ngày 2 viên chia 2 lần',
                    ],
                ],
            ])
            ->assertRedirect();

        $selfAppt->refresh();
        $medicine->refresh();
        $this->assertEquals(Appointment::STATUS_COMPLETED, $selfAppt->status);
        $this->assertEquals($initialStock - 10, (int) $medicine->stock);

        // 2. Tạo lịch khám cho người thân (Con nhỏ) trên cùng tài khoản
        $relativeAppt = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'booking_for' => 'relative',
            'relative_name' => 'Bé Nguyễn Gia Bảo',
            'relative_relationship' => 'Con nhỏ',
        ], $this->patientUser);

        // Khi mở trang bệnh án của Bé Nguyễn Gia Bảo, tiền sử của chủ tài khoản ("Viêm họng cấp đã kê đơn") KHÔNG được trộn vào
        $response = $this->actingAs($doctorUser, 'web_doctor')
            ->get(route('doctor.patient.record', $relativeAppt->id));

        $response->assertStatus(200);
        $previousRecords = $response->viewData('previousRecords');
        $this->assertCount(0, $previousRecords, 'Tiền sử của chủ tài khoản không được trộn vào hồ sơ của người thân');
    }

    /**
     * Test 10: Lễ tân quét mã QR chuẩn Kiosk (PK125THAITHINH|MA:TT-...) để Check-in 1 chạm
     */
    public function test_admin_quick_qr_checkin_accepts_kiosk_payload(): void
    {
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
        ], $this->patientUser);

        $qrPayload = "PK125THAITHINH|MA:{$appointment->appointment_code}|NGAY:24/10/2026|GIO:09:00 - 09:30";

        $response = $this->actingAs($this->adminUser, 'web_admin')
            ->post(route('admin.appointments.quick_checkin'), [
                'qr_code' => $qrPayload,
            ]);

        $response->assertRedirect(route('admin.appointments.show', $appointment->id));
        $appointment->refresh();
        $this->assertEquals(Appointment::STATUS_CHECKED_IN, $appointment->status);
        $this->assertNotNull($appointment->checked_in_at);
    }

    /**
     * Test 11: Quản trị viên tạo lịch làm việc hàng loạt theo khoảng ngày (Bulk Range Mode)
     */
    public function test_admin_can_create_bulk_working_schedules_by_date_range(): void
    {
        $start = Carbon::today()->addDays(30);
        $end = $start->copy()->addDays(4); // 5 ngày liên tiếp

        $response = $this->actingAs($this->adminUser, 'web_admin')
            ->post(route('admin.schedules.store'), [
                'doctor_id' => $this->doctor->id,
                'schedule_mode' => 'range',
                'date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days_of_week' => [0, 1, 2, 3, 4, 5, 6],
                'shift_preset' => 'morning',
                'start_time' => '07:30',
                'end_time' => '11:30',
                'slot_duration' => 30,
                'capacity' => 5,
                'note' => 'Lịch trực hàng loạt tuần tới',
            ]);

        $response->assertRedirect(route('admin.schedules.index', ['doctor_id' => $this->doctor->id]));

        $createdSchedules = WorkingSchedule::where('doctor_id', $this->doctor->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $this->assertEquals(5, $createdSchedules);
    }

    /**
     * Test 12: Chống lách tồn kho khi kê trùng 2 dòng cùng 1 mã thuốc & bảo toàn tồn kho khi sửa đơn thuốc của ca đã completed
     */
    public function test_emr_prescription_duplicate_medicine_rows_and_completed_revision_stock_integrity(): void
    {
        $medicine = \App\Models\Medicine::create([
            'name' => 'Amoxicillin 500mg Test',
            'slug' => 'amoxicillin-500mg-test',
            'unit' => 'Viên',
            'price' => 5000,
            'stock' => 100,
        ]);

        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
        ], $this->patientUser);

        $record = \App\Models\MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'diagnosis' => 'Viêm họng cấp',
        ]);

        $doctorUser = $this->doctor->user;

        // 1. Kê 2 dòng cùng medicine_id (60 + 60 = 120 > 100) -> Phải bị chặn lỗi tồn kho
        $resDuplicate = $this->actingAs($doctorUser, 'web_doctor')
            ->put(route('doctor.records.update', $record->id), [
                'diagnosis' => 'Viêm họng cấp',
                'status' => 'completed',
                'prescription_items' => [
                    ['medicine_id' => $medicine->id, 'quantity' => 60, 'unit' => 'Viên', 'usage' => 'Sáng'],
                    ['medicine_id' => $medicine->id, 'quantity' => 60, 'unit' => 'Viên', 'usage' => 'Tối'],
                ],
            ]);

        $resDuplicate->assertSessionHasErrors('prescription_items');
        $medicine->refresh();
        $this->assertEquals(100, $medicine->stock);

        // 2. Hoàn tất ca khám với 10 viên -> Tồn kho còn 90
        $this->actingAs($doctorUser, 'web_doctor')
            ->put(route('doctor.records.update', $record->id), [
                'diagnosis' => 'Viêm họng cấp',
                'status' => 'completed',
                'prescription_items' => [
                    ['medicine_id' => $medicine->id, 'quantity' => 10, 'unit' => 'Viên', 'usage' => 'Sau ăn'],
                ],
            ]);

        $medicine->refresh();
        $this->assertEquals(90, $medicine->stock);

        // 3. Chỉnh sửa lại đơn thuốc của ca đã completed từ 10 viên lên 25 viên -> Hoàn 10, trừ 25 => Tồn kho chuẩn = 75
        $this->actingAs($doctorUser, 'web_doctor')
            ->put(route('doctor.records.update', $record->id), [
                'diagnosis' => 'Viêm họng cấp điều chỉnh liều',
                'status' => 'completed',
                'prescription_items' => [
                    ['medicine_id' => $medicine->id, 'quantity' => 25, 'unit' => 'Viên', 'usage' => 'Sau ăn'],
                ],
            ]);

        $medicine->refresh();
        $this->assertEquals(75, $medicine->stock);
    }

    /**
     * Test 13: Viện phí đa tầng (partially_paid khi phát sinh đơn thuốc sau khi đã trả phí khám VNPay, và fully_paid sau khi thu ngân tất toán)
     */
    public function test_multi_tier_billing_settlement_partially_paid_to_fully_paid(): void
    {
        $medicine = \App\Models\Medicine::create([
            'name' => 'Cefuroxim 500mg',
            'slug' => 'cefuroxim-500mg',
            'unit' => 'Viên',
            'price' => 10000,
            'stock' => 50,
        ]);

        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'payment_method' => 'VNPay',
            'payment_status' => Appointment::PAYMENT_SUCCESS,
        ], $this->patientUser);

        $record = \App\Models\MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'diagnosis' => 'Viêm phế quản',
        ]);

        \App\Models\PrescriptionItem::create([
            'prescription_id' => $record->id,
            'medicine_id' => $medicine->id,
            'quantity' => 10, // 10 * 10,000 = 100,000đ
            'unit' => 'Viên',
            'usage' => 'Uống sau ăn',
        ]);

        $appointment->load(['service', 'patient', 'medicalRecord.prescriptionItems.medicine', 'medicalRecord.labTests']);
        $breakdown = $appointment->calculateCostBreakdown();

        $this->assertEquals('partially_paid', $breakdown['settlement_status']);
        $this->assertEquals(100000.0, $breakdown['balance_due']);

        // Thu ngân tất toán bổ sung tại quầy
        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.appointments.payment', $appointment->id), [
                'include_full_clinical_costs' => 1,
                'apply_bhyt' => 0,
            ]);

        $appointment->refresh();
        $appointment->load(['service', 'patient', 'medicalRecord.prescriptionItems.medicine', 'medicalRecord.labTests']);
        $afterBreakdown = $appointment->calculateCostBreakdown();

        $this->assertEquals('fully_paid', $afterBreakdown['settlement_status']);
        $this->assertEquals(0.0, $afterBreakdown['balance_due']);
    }

    /**
     * Test 14: Webhook VNPay IPN kiểm tra chữ ký, chống giả mạo số tiền (vnp_Amount) và xác nhận thanh toán tự động
     */
    public function test_vnpay_ipn_validates_signature_amount_and_confirms_payment(): void
    {
        config(['services.vnpay.hash_secret' => 'TEST_SECRET_KEY_125_THAI_THINH']);

        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
            'payment_method' => 'online',
            'payment_status' => Appointment::PAYMENT_FAILED,
        ], $this->patientUser);

        $expectedFee = (float) $this->service->price;

        // 1. Sai chữ ký -> RspCode = 97
        $badSigRes = $this->getJson(route('vnpay.ipn', [
            'vnp_Amount' => (int) ($expectedFee * 100),
            'vnp_OrderInfo' => 'Thanh toan lich hen #' . $appointment->id,
            'vnp_ResponseCode' => '00',
            'vnp_SecureHash' => 'invalid_signature',
        ]));
        $badSigRes->assertJson(['RspCode' => '97']);

        // 2. Giả mạo số tiền (chỉ trả 1,000đ thay vì giá dịch vụ) -> RspCode = 04
        $tamperedParams = [
            'vnp_Amount' => 1000 * 100,
            'vnp_OrderInfo' => 'Thanh toan lich hen #' . $appointment->id,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => 'TXN-' . $appointment->id,
        ];
        ksort($tamperedParams);
        $hashData = http_build_query($tamperedParams);
        $tamperedParams['vnp_SecureHash'] = hash_hmac('sha512', $hashData, 'TEST_SECRET_KEY_125_THAI_THINH');

        $badAmountRes = $this->getJson(route('vnpay.ipn', $tamperedParams));
        $badAmountRes->assertJson(['RspCode' => '04']);

        // 3. Hợp lệ cả chữ ký và số tiền -> RspCode = 00 & payment_status = success
        $validParams = [
            'vnp_Amount' => (int) ($expectedFee * 100),
            'vnp_BankCode' => 'NCB',
            'vnp_OrderInfo' => 'Thanh toan lich hen #' . $appointment->id,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '14567890',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => 'TXN-' . $appointment->id,
        ];
        ksort($validParams);
        $validHashData = http_build_query($validParams);
        $validParams['vnp_SecureHash'] = hash_hmac('sha512', $validHashData, 'TEST_SECRET_KEY_125_THAI_THINH');

        $okRes = $this->getJson(route('vnpay.ipn', $validParams));
        $okRes->assertJson(['RspCode' => '00']);

        $appointment->refresh();
        $this->assertEquals(Appointment::PAYMENT_SUCCESS, $appointment->payment_status);

        // 4. Gọi lại IPN lần 2 (Idempotency) -> RspCode = 02 (Order already confirmed)
        $dupRes = $this->getJson(route('vnpay.ipn', $validParams));
        $dupRes->assertJson(['RspCode' => '02']);
    }

    /**
     * Test 15: Bảo mật tệp kết quả xét nghiệm CLS — Chặn bệnh nhân khác xem trộm kết quả (403 Forbidden)
     */
    public function test_protected_lab_test_attachment_denies_other_patients(): void
    {
        $appointment = $this->appointmentService->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlot->date->toDateString(),
            'time_slot_id' => $this->timeSlot->id,
        ], $this->patientUser);

        $record = \App\Models\MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'diagnosis' => 'Khám tổng quát',
        ]);

        $labTest = \App\Models\LabTest::create([
            'medical_record_id' => $record->id,
            'department_id' => $this->doctor->department_id,
            'requested_by' => $this->doctor->user_id,
            'test_name' => 'Xét nghiệm công thức máu',
            'image' => 'lab_tests/sample_result.jpg',
            'status' => 'completed',
        ]);

        $otherUser = User::factory()->create(['role_id' => $this->patientUser->role_id]);

        $res = $this->actingAs($otherUser, 'web')
            ->get(route('lab_tests.attachment', $labTest->id));

        $res->assertStatus(403);
    }
}
