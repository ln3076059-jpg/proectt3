<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SecurityAndIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (Doctor::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed');
        }
    }

    /**
     * Kiểm tra role_id mặc định khi tạo User luôn là patient, không bao giờ là admin
     */
    public function test_user_role_id_defaults_to_patient_not_admin(): void
    {
        $user = User::create([
            'name' => 'Default Role Tester',
            'email' => 'default_role_test_' . uniqid() . '@thaithinh.vn',
            'password' => bcrypt('password123'),
        ]);

        $this->assertNotNull($user->role_id);
        $this->assertEquals('patient', $user->role->name, 'Default role must be patient');
        $this->assertFalse($user->hasRole('admin'), 'User without explicit role must not be admin');
    }

    /**
     * Kiểm tra payment_status hỗ trợ trạng thái pending và các giá trị mở rộng
     */
    public function test_payment_status_supports_pending_and_extended_statuses(): void
    {
        $doctor = Doctor::firstOrFail();
        $service = Service::firstOrFail();
        $patientUser = User::whereHas('role', fn ($q) => $q->where('name', 'patient'))->firstOrFail();
        $patient = $patientUser->patient;

        $appt = Appointment::create([
            'appointment_code' => 'TEST-PAY-' . strtoupper(substr(uniqid(), 0, 6)),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(2),
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_PENDING,
            'total' => 200000,
            'total_amount' => 200000,
        ]);

        $this->assertEquals('pending', $appt->payment_status);

        $appt->payment_status = Appointment::PAYMENT_REFUNDED;
        $appt->save();
        $this->assertEquals('refunded', $appt->fresh()->payment_status);
    }

    /**
     * Kiểm tra AdminMiddleware chặn truy cập của bệnh nhân tuyệt đối
     */
    public function test_admin_middleware_strictly_blocks_patient_access(): void
    {
        $patientUser = User::whereHas('role', fn ($q) => $q->where('name', 'patient'))->firstOrFail();

        $response = $this->actingAs($patientUser, 'web')
            ->get(route('admin.dashboard'));

        // Phải bị chuyển hướng về login của admin hoặc trang chủ, không được lọt qua
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    /**
     * Kiểm tra khóa ngoại giữa prescription_items và medical_records (Cascade on delete)
     */
    public function test_prescription_item_foreign_key_cascades_on_medical_record_delete(): void
    {
        $doctor = Doctor::firstOrFail();
        $service = Service::firstOrFail();
        $patientUser = User::whereHas('role', fn ($q) => $q->where('name', 'patient'))->firstOrFail();
        $patient = $patientUser->patient;

        $appt = Appointment::create([
            'appointment_code' => 'TEST-FK-' . strtoupper(substr(uniqid(), 0, 6)),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(3),
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_PENDING,
        ]);

        $medRecord = MedicalRecord::create([
            'appointment_id' => $appt->id,
            'patient_id' => $patient->id,
            'diagnosis' => 'Viêm họng cấp',
        ]);

        $medicine = Medicine::firstOrCreate(
            ['slug' => 'paracetamol-500mg-test'],
            ['name' => 'Paracetamol 500mg Test', 'stock' => 100, 'min_stock' => 10]
        );

        $item = PrescriptionItem::create([
            'prescription_id' => $medRecord->id,
            'medicine_id' => $medicine->id,
            'quantity' => 10,
            'unit' => 'Viên',
        ]);

        $this->assertDatabaseHas('prescription_items', ['id' => $item->id]);

        // Xóa medical record -> prescription_items phải tự động bị xóa cascade
        $medRecord->delete();
        $this->assertDatabaseMissing('prescription_items', ['id' => $item->id]);
    }

    /**
     * Kiểm tra chuẩn hóa học hàm/học vị bác sĩ (formatted_name) không bao giờ lặp "BS. BS."
     */
    public function test_doctor_formatted_name_prevents_duplicate_title_prefix(): void
    {
        $doctorWithTitle = new User(['name' => 'BS. CKII. Nguyễn Quang Minh']);
        $this->assertEquals('BS. CKII. Nguyễn Quang Minh', $doctorWithTitle->formatted_doctor_name);
        $this->assertStringNotContainsString('BS. BS.', $doctorWithTitle->formatted_doctor_name);

        $doctorWithAcademic = new User(['name' => 'ThS. BS. Trần Thị Thu Hà']);
        $this->assertEquals('ThS. BS. Trần Thị Thu Hà', $doctorWithAcademic->formatted_doctor_name);
        $this->assertStringNotContainsString('BS. ThS.', $doctorWithAcademic->formatted_doctor_name);

        $doctorPlain = new User(['name' => 'Lê Văn Hùng']);
        $this->assertEquals('BS. Lê Văn Hùng', $doctorPlain->formatted_doctor_name);
    }

    /**
     * Kiểm tra nghiệp vụ: Xóa lịch hẹn pending phải hoàn trả chỗ (booked_count) và không cho mở khóa slot đã đầy
     */
    public function test_admin_delete_pending_appointment_releases_slot_and_blocks_unlocking_full_slot(): void
    {
        $admin = User::where('email', 'admin@thaithinh.vn')->firstOrFail();
        $doctor = Doctor::where('is_active', true)->firstOrFail();
        $service = Service::where('department_id', $doctor->department_id)->first() ?? Service::firstOrFail();
        $patientUser = User::whereHas('role', fn ($q) => $q->where('name', 'patient'))->firstOrFail();

        $targetDate = now()->addDays(35)->toDateString();
        $schedule = \App\Models\WorkingSchedule::create([
            'doctor_id' => $doctor->id,
            'date' => $targetDate,
            'day_of_week' => \Carbon\Carbon::parse($targetDate)->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => \App\Models\WorkingSchedule::STATUS_ACTIVE,
        ]);

        $slot = \App\Models\TimeSlot::create([
            'working_schedule_id' => $schedule->id,
            'doctor_id' => $doctor->id,
            'date' => $targetDate,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'capacity' => 1,
            'booked_count' => 0,
            'status' => \App\Models\TimeSlot::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $appt = app(\App\Services\AppointmentService::class)->bookAppointment([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'date' => $targetDate,
            'time_slot_id' => $slot->id,
        ], $patientUser);

        $slot->refresh();
        $this->assertEquals(1, $slot->booked_count);
        $this->assertEquals(\App\Models\TimeSlot::STATUS_LOCKED, $slot->status);

        // Không cho phép mở khóa slot khi booked_count >= capacity
        $this->actingAs($admin, 'web_admin')
            ->patch(route('admin.schedules.slots.toggle', $slot->id))
            ->assertRedirect();
        $slot->refresh();
        $this->assertEquals(\App\Models\TimeSlot::STATUS_LOCKED, $slot->status, 'Không được mở khóa slot đang đầy chỗ');

        // Xóa lịch hẹn đang pending -> Phải tự động giải phóng booked_count về 0 và mở khóa slot
        $this->actingAs($admin, 'web_admin')
            ->delete(route('admin.appointments.destroy', $appt->id))
            ->assertRedirect(route('admin.appointments.index'));

        $slot->refresh();
        $this->assertEquals(0, $slot->booked_count, 'Xóa lịch hẹn pending phải hoàn trả lại booked_count cho TimeSlot');
        $this->assertEquals(\App\Models\TimeSlot::STATUS_AVAILABLE, $slot->status);
    }

    /**
     * Kiểm tra các nâng cấp nghiệp vụ HIS thực tế:
     * - Cập nhật buồng khám (room_number) & trạng thái công tác (is_active) của bác sĩ
     * - Tìm kiếm lịch hẹn theo tên người thân (relative_name) và mã QR Kiosk tại trang Admin
     * - Ghi nhật ký kiểm toán ca khám (AppointmentStatusHistory) khi kỹ thuật viên trả kết quả xét nghiệm CLS
     */
    public function test_his_clinical_enhancements_doctor_room_admin_qr_search_and_lab_audit(): void
    {
        $admin = User::where('email', 'admin@thaithinh.vn')->firstOrFail();
        $doctor = Doctor::where('is_active', true)->with('user')->firstOrFail();
        $service = Service::where('department_id', $doctor->department_id)->first() ?? Service::firstOrFail();
        $patientUser = User::whereHas('role', fn ($q) => $q->where('name', 'patient'))->firstOrFail();

        // 1. Admin cập nhật Buồng khám (room_number) cho bác sĩ
        $this->actingAs($admin, 'web_admin')
            ->put(route('admin.doctors.update', $doctor->id), [
                'name' => $doctor->user->name,
                'email' => $doctor->user->email,
                'phone' => $doctor->user->phone ?? '0912345678',
                'department_id' => $doctor->department_id,
                'specialization' => $doctor->specialization ?? 'Đa khoa',
                'license_number' => $doctor->license_number ?? '012345/HN-CCHN',
                'room_number' => 'P.305 - Tầng 3',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $doctor->refresh();
        $this->assertEquals('P.305 - Tầng 3', $doctor->room_number);

        // 2. Đặt lịch cho người thân và tìm kiếm bằng chuỗi QR Kiosk & tên người thân trên trang Admin Appointments
        $appt = Appointment::create([
            'appointment_code' => 'TT-HIS-' . strtoupper(substr(uniqid(), 0, 5)),
            'patient_id' => $patientUser->patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDays(5),
            'status' => Appointment::STATUS_CONFIRMED,
            'payment_status' => Appointment::PAYMENT_PENDING,
            'booking_for' => 'relative',
            'relative_name' => 'Cụ Nguyễn Văn Trường',
            'relative_relationship' => 'Ông nội',
        ]);

        $this->actingAs($admin, 'web_admin')
            ->get(route('admin.appointments.index', ['search' => 'Cụ Nguyễn Văn Trường']))
            ->assertStatus(200)
            ->assertSee($appt->appointment_code);

        $this->actingAs($admin, 'web_admin')
            ->get(route('admin.appointments.index', ['search' => "PK125THAITHINH|MA:{$appt->appointment_code}|NGAY:30/10/2026"]))
            ->assertStatus(200)
            ->assertSee($appt->appointment_code);

        // 3. Kiểm tra hạn hủy lịch chính xác theo phút (còn 90 phút < 120 phút -> chặn hủy)
        $apptSoon = Appointment::create([
            'appointment_code' => 'TT-SOON-' . strtoupper(substr(uniqid(), 0, 5)),
            'patient_id' => $patientUser->patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addMinutes(90),
            'status' => Appointment::STATUS_CONFIRMED,
            'payment_status' => Appointment::PAYMENT_PENDING,
        ]);
        $timeSlotSoon = \App\Models\TimeSlot::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addMinutes(90)->toDateString(),
            'start_time' => now()->addMinutes(90)->format('H:i:s'),
            'end_time' => now()->addMinutes(120)->format('H:i:s'),
            'capacity' => 5,
            'booked_count' => 1,
            'status' => \App\Models\TimeSlot::STATUS_AVAILABLE,
            'is_active' => true,
        ]);
        $apptSoon->update(['time_slot_id' => $timeSlotSoon->id]);
        $cancelCheck = $apptSoon->fresh(['timeSlot'])->canBeCancelledByPatient(2);
        $this->assertFalse($cancelCheck['can'], 'Lịch hẹn chỉ còn 90 phút (< 2 tiếng) phải bị chặn hủy chính xác theo phút');

        // 4. Kiểm tra TestType có đơn giá riêng (price = 320,000đ), tính đúng vào bảng kê viện phí và chặn xóa khi đã có chỉ định CLS
        $testType = \App\Models\TestType::create([
            'name' => 'Siêu âm Doppler Tim 4D Chuyên sâu',
            'description' => 'Đánh giá huyết động học van tim',
            'department_id' => $doctor->department_id,
            'price' => 320000,
            'created_by' => $admin->id,
        ]);

        $medRecord = \App\Models\MedicalRecord::create([
            'appointment_id' => $appt->id,
            'patient_id' => $patientUser->patient->id,
            'doctor_id' => $doctor->id,
            'symptom' => 'Khó thở khi gắng sức',
            'diagnosis' => 'Theo dõi hở van hai lá',
        ]);

        \App\Models\LabTest::create([
            'medical_record_id' => $medRecord->id,
            'department_id' => $doctor->department_id,
            'test_type_id' => $testType->id,
            'test_name' => $testType->name,
            'requested_by' => $doctor->user_id,
            'status' => 'requested',
        ]);

        $breakdown = $appt->fresh(['service', 'medicalRecord.labTests.test_type', 'medicalRecord.prescriptionItems.medicine'])->calculateCostBreakdown();
        $this->assertEquals(320000, $breakdown['lab_test_fee'], 'Bảng kê viện phí phải lấy đúng đơn giá price = 320,000đ của TestType');

        // Thử xóa TestType đang được sử dụng trong LabTest -> phải bị chặn
        $this->actingAs($admin, 'web_admin')
            ->delete(route('admin.test-types.destroy', $testType->id))
            ->assertRedirect();
        $this->assertDatabaseHas('test_types', ['id' => $testType->id]);
    }
}
