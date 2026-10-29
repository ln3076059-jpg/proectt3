<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Disease;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TestType;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\WorkingSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AllModulesAndSubFunctionsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected User $patientUser;
    protected Patient $patient;
    protected Department $department;
    protected Service $service;
    protected TimeSlot $timeSlotA;
    protected TimeSlot $timeSlotB;
    protected Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        if (Doctor::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed');
        }

        $this->adminUser = User::where('email', 'admin@thaithinh.vn')->firstOrFail();
        $this->doctor = Doctor::where('is_active', true)->with(['user', 'department'])->firstOrFail();
        $this->doctorUser = $this->doctor->user;
        $this->department = $this->doctor->department ?? Department::firstOrFail();
        $this->service = Service::where('department_id', $this->department->id)->first() ?? Service::firstOrFail();
        $this->patient = Patient::with('user')->firstOrFail();
        $this->patientUser = $this->patient->user;

        $this->medicine = Medicine::firstOrCreate(
            ['slug' => 'paracetamol-500mg-e2e'],
            [
                'name' => 'Paracetamol 500mg E2E',
                'unit' => 'Viên',
                'price' => 1500,
                'stock' => 100,
            ]
        );

        $dateA = Carbon::today()->addDays(25)->toDateString();
        $scheduleA = WorkingSchedule::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'date' => $dateA, 'start_time' => '08:00:00'],
            [
                'day_of_week' => Carbon::parse($dateA)->dayOfWeek,
                'end_time' => '11:00:00',
                'status' => WorkingSchedule::STATUS_ACTIVE,
            ]
        );

        $this->timeSlotA = TimeSlot::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'date' => $dateA, 'start_time' => '08:00:00'],
            [
                'working_schedule_id' => $scheduleA->id,
                'end_time' => '08:30:00',
                'capacity' => 5,
                'booked_count' => 0,
                'status' => TimeSlot::STATUS_AVAILABLE,
                'is_active' => true,
            ]
        );

        $this->timeSlotB = TimeSlot::firstOrCreate(
            ['doctor_id' => $this->doctor->id, 'date' => $dateA, 'start_time' => '08:30:00'],
            [
                'working_schedule_id' => $scheduleA->id,
                'end_time' => '09:00:00',
                'capacity' => 5,
                'booked_count' => 0,
                'status' => TimeSlot::STATUS_AVAILABLE,
                'is_active' => true,
            ]
        );
    }

    /**
     * MODULE 1: Tất cả các trang Công khai (Guest Portal) & Chức năng con (Lọc, Tìm kiếm, Tư vấn triệu chứng, Auth forms)
     */
    public function test_module_1_public_pages_and_subfunctions(): void
    {
        // 1.1 Trang chủ
        $this->get(route('home'))->assertStatus(200)->assertSee('125 Thái Thịnh');

        // 1.2 Danh sách bác sĩ + lọc theo chuyên khoa + tìm kiếm tên
        $this->get(route('doctors.index'))->assertStatus(200);
        $this->get(route('doctors.index', ['department_id' => $this->department->id, 'q' => $this->doctorUser->name]))
            ->assertStatus(200);

        // 1.3 Danh sách chuyên khoa
        $this->get(route('departments.index'))->assertStatus(200);

        // 1.4 Danh sách gói dịch vụ + lọc theo khoa
        $this->get(route('services.index'))->assertStatus(200);
        $this->get(route('services.index', ['department_id' => $this->department->id]))->assertStatus(200);

        // 1.5 Trang giới thiệu
        $this->get(route('introduces.index'))->assertStatus(200);

        // 1.6 Trang tư vấn triệu chứng & lọc theo triệu chứng
        $this->get(route('advisor.index'))->assertStatus(200);
        $this->get(route('advisor.index', ['q' => 'đau đầu']))->assertStatus(200);
        $this->get(route('ai.chat'))->assertRedirect(route('advisor.index'));

        // 1.7 Các trang đăng nhập / đăng ký / quên mật khẩu của 3 phân hệ
        $this->get(route('patient.login'))->assertStatus(200);
        $this->get(route('register'))->assertStatus(200);
        $this->get(route('password.request'))->assertStatus(200);
        $this->get(route('admin.login'))->assertStatus(200);
        $this->get(route('doctor.login'))->assertStatus(200);
    }

    /**
     * MODULE 2: Phân hệ Bệnh nhân (Hồ sơ cá nhân, API Slot/Bác sĩ, Đặt lịch Bản thân & Người thân, Dời lịch, Hủy lịch, QR Pass)
     */
    public function test_module_2_patient_booking_reschedule_cancel_and_profile_subfunctions(): void
    {
        // 2.1 Xem và cập nhật thông tin cá nhân
        $this->actingAs($this->patientUser)
            ->get(route('profile.edit'))
            ->assertStatus(200);

        $this->actingAs($this->patientUser)
            ->patch(route('profile.update'), [
                'name' => $this->patientUser->name,
                'email' => $this->patientUser->email,
                'phone' => '0988123456',
                'address' => '125 Thái Thịnh, Đống Đa, Hà Nội',
            ])
            ->assertRedirect();

        // 2.2 API lấy bác sĩ theo chuyên khoa & API lấy khung giờ khả dụng
        $this->actingAs($this->patientUser)
            ->getJson("/appointments/doctors/{$this->department->id}")
            ->assertStatus(200);

        $this->actingAs($this->patientUser)
            ->getJson(route('appointments.time-slots', [
                'doctor_id' => $this->doctor->id,
                'date' => $this->timeSlotA->date->toDateString(),
            ]))
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2.3 Đặt lịch khám cho người thân (booking_for = relative)
        $bookResp = $this->actingAs($this->patientUser)
            ->post(route('appointments.store'), [
                'department_id' => $this->department->id,
                'doctor_id' => $this->doctor->id,
                'service_id' => $this->service->id,
                'appointment_date' => $this->timeSlotA->date->toDateString(),
                'time_slot_id' => $this->timeSlotA->id,
                'booking_for' => 'relative',
                'relative_name' => 'Bé Trần Bảo Ngọc',
                'relative_relationship' => 'Con nhỏ',
                'relative_dob' => '2021-08-12',
                'relative_gender' => 'female',
                'note' => 'Khám tổng quát nhi khoa',
            ]);

        $appointment = Appointment::latest('id')->firstOrFail();
        $bookResp->assertRedirect(route('appointments.show', $appointment->id));
        $this->assertEquals('relative', $appointment->booking_for);
        $this->assertEquals('Bé Trần Bảo Ngọc', $appointment->relative_name);

        // 2.4 Trang đặt lịch hiển thị chip chọn nhanh hồ sơ người thân đã lưu
        $this->actingAs($this->patientUser)
            ->get(route('appointments.create'))
            ->assertStatus(200)
            ->assertSee('Bé Trần Bảo Ngọc');

        // 2.5 Trang danh sách lịch hẹn & bộ lọc theo trạng thái + ngày
        $this->actingAs($this->patientUser)
            ->get(route('appointments.index', ['status' => 'pending']))
            ->assertStatus(200)
            ->assertSee($appointment->appointment_code);

        // 2.6 Trang chi tiết phiếu khám (Kiểm tra có mã QR Kiosk, nút tải PNG & in phiếu)
        $this->actingAs($this->patientUser)
            ->get(route('appointments.show', $appointment->id))
            ->assertStatus(200)
            ->assertSee($appointment->appointment_code)
            ->assertSee('downloadMedicalPassPng')
            ->assertSee('PK125THAITHINH');

        // 2.7 Dời lịch khám sang khung giờ B
        $this->actingAs($this->patientUser)
            ->patch(route('appointments.reschedule', $appointment->id), [
                'new_time_slot_id' => $this->timeSlotB->id,
                'reschedule_reason' => 'Đổi sang khung giờ 08:30',
            ])
            ->assertRedirect(route('appointments.show', $appointment->id));

        $appointment->refresh();
        $this->assertEquals($this->timeSlotB->id, $appointment->time_slot_id);

        // 2.8 Hủy lịch khám & tự động nhả slot
        $this->actingAs($this->patientUser)
            ->patch(route('appointments.cancel', $appointment->id), [
                'reason' => 'Gia đình có việc bận đột xuất',
            ])
            ->assertRedirect(route('appointments.show', $appointment->id));

        $appointment->refresh();
        $this->assertEquals(Appointment::STATUS_CANCELLED, $appointment->status);
    }

    /**
     * MODULE 3: Luồng Thanh toán VNPay (Khởi tạo qua AppointmentService, Checkout lịch có sẵn, Xác thực chữ ký Callback & Hoàn tiền)
     */
    public function test_module_3_vnpay_payment_callback_and_admin_refund_subfunctions(): void
    {
        // 3.1 Khởi tạo đặt lịch + thanh toán VNPay trực tiếp qua PaymentController::vnpay_payment
        $vnpResp = $this->actingAs($this->patientUser)
            ->post(route('vnpay_payment'), [
                'patient_id' => $this->patient->id,
                'department_id' => $this->department->id,
                'doctor_id' => $this->doctor->id,
                'service_id' => $this->service->id,
                'time_slot_id' => $this->timeSlotA->id,
                'appointment_date' => $this->timeSlotA->date->toDateString(),
                'note' => 'Thanh toán trực tuyến VNPay',
            ]);

        $vnpResp->assertStatus(302);
        $appointment = Appointment::latest('id')->firstOrFail();
        $this->assertStringStartsWith('TT-', $appointment->appointment_code);
        $this->assertNotNull($appointment->transaction_ref);

        // 3.2 Checkout lại một lịch hẹn chưa thanh toán
        $checkoutResp = $this->actingAs($this->patientUser)
            ->get(route('payment.checkout', $appointment->id));
        $checkoutResp->assertStatus(302);

        // 3.3 Giả lập Callback VNPay thành công (vnp_ResponseCode = 00) với chữ ký HMAC-SHA512 hợp lệ
        $secret = trim((string) config('services.vnpay.hash_secret'));
        $vnpParams = [
            'vnp_Amount' => (string) ((int) round($this->service->price * 100)),
            'vnp_BankCode' => 'NCB',
            'vnp_OrderInfo' => 'Thanh toan lich kham #' . $appointment->id,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => 'VNP14567890',
            'vnp_TxnRef' => $appointment->transaction_ref,
        ];
        ksort($vnpParams);
        $hashData = '';
        $i = 0;
        foreach ($vnpParams as $k => $v) {
            $hashData .= ($i === 1 ? '&' : '') . urlencode($k) . '=' . urlencode($v);
            $i = 1;
        }
        $vnpParams['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $secret);

        $this->actingAs($this->patientUser)
            ->get(route('vnpay.return', $vnpParams))
            ->assertRedirect(route('appointments.show', $appointment->id));

        $appointment->refresh();
        $this->assertEquals(Appointment::PAYMENT_SUCCESS, $appointment->payment_status);

        // 3.4 Hủy lịch đã thanh toán -> Chờ hoàn tiền -> Admin xác nhận hoàn tiền (refunded)
        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.appointments.status', $appointment->id), [
                'status' => Appointment::STATUS_CANCELLED,
                'note' => 'Hủy theo yêu cầu bệnh nhân',
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertEquals(Appointment::REFUND_PENDING, $appointment->refund_status);

        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.appointments.refund', $appointment->id), [
                'refund_note' => 'Đã hoàn tiền qua VNPay 100%',
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertEquals(Appointment::REFUND_REFUNDED, $appointment->refund_status);
    }

    /**
     * MODULE 4: Phân hệ Bác sĩ (Dashboard, Lọc/Quét QR, Tiếp đón, Khám EMR, Kê đơn trừ kho, Chỉ định xét nghiệm, Lịch sử khám, Hồ sơ BS)
     */
    public function test_module_4_doctor_clinical_workflow_and_subfunctions(): void
    {
        $appointment = app(\App\Services\AppointmentService::class)->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlotA->date->toDateString(),
            'time_slot_id' => $this->timeSlotA->id,
            'note' => 'Đau tức ngực nhẹ khi gắng sức',
        ], $this->patientUser);

        // 4.1 Dashboard Bác sĩ + Tìm kiếm bằng chuỗi QR Kiosk
        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.dashboard', [
                'date' => $this->timeSlotA->date->toDateString(),
                'search' => "PK125THAITHINH|MA:{$appointment->appointment_code}|NGAY:24/10/2026",
            ]))
            ->assertStatus(200)
            ->assertSee($appointment->appointment_code);

        // 4.2 Bác sĩ Check-in bệnh nhân tại phòng chờ
        $this->actingAs($this->doctorUser, 'web_doctor')
            ->post(route('doctor.appointments.checkin', $appointment->id))
            ->assertRedirect();

        $appointment->refresh();
        $this->assertEquals(Appointment::STATUS_CHECKED_IN, $appointment->status);

        // 4.3 Mở màn hình Bệnh án EMR của ca khám
        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patient.record', $appointment->id))
            ->assertStatus(200);

        $record = MedicalRecord::where('appointment_id', $appointment->id)->firstOrFail();

        // 4.4 Bác sĩ chỉ định xét nghiệm cận lâm sàng (LabTest)
        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.lab_tests.create', $record->id))
            ->assertStatus(200);

        $this->actingAs($this->doctorUser, 'web_doctor')
            ->post(route('doctor.lab_tests.store', $record->id), [
                'test_name' => 'Điện tâm đồ ECG & Siêu âm Doppler tim',
                'department_id' => $this->department->id,
                'note' => 'Kiểm tra nhịp tim và van tim',
            ])
            ->assertRedirect(route('doctor.patient.record', $appointment->id));

        $this->assertDatabaseHas('lab_tests', [
            'medical_record_id' => $record->id,
            'test_name' => 'Điện tâm đồ ECG & Siêu âm Doppler tim',
            'status' => 'requested',
        ]);

        // 4.5 Bác sĩ lưu chẩn đoán, kê đơn thuốc và hoàn tất ca khám (completed -> trừ kho thuốc)
        $stockBefore = (int) $this->medicine->stock;
        $this->actingAs($this->doctorUser, 'web_doctor')
            ->put(route('doctor.records.update', $record->id), [
                'diagnosis' => 'Rối loạn thần kinh tim nhẹ (ICD-10: F45.3)',
                'description' => 'Nhịp tim 84 lần/phút, huyết áp 120/80 mmHg',
                'doctor_conclusion' => 'Nghỉ ngơi hợp lý, uống thuốc theo đơn, tái khám sau 2 tuần',
                'status' => Appointment::STATUS_COMPLETED,
                'prescription_items' => [
                    [
                        'medicine_id' => $this->medicine->id,
                        'quantity' => 15,
                        'unit' => 'Viên',
                        'usage' => 'Uống sau ăn sáng và tối',
                        'note' => 'Không dùng cùng rượu bia',
                    ],
                ],
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->medicine->refresh();
        $this->assertEquals(Appointment::STATUS_COMPLETED, $appointment->status);
        $this->assertEquals($stockBefore - 15, (int) $this->medicine->stock);

        // 4.6 Bệnh nhân xem Bệnh án điện tử EMR & Đơn thuốc + Bảng kê chi phí thuốc trên trang chi tiết
        $this->actingAs($this->patientUser)
            ->get(route('appointments.record', $appointment->id))
            ->assertStatus(200)
            ->assertSee('Rối loạn thần kinh tim nhẹ')
            ->assertSee('Paracetamol 500mg E2E');

        $this->actingAs($this->patientUser)
            ->get(route('appointments.show', $appointment->id))
            ->assertStatus(200)
            ->assertSee('Tiền thuốc theo đơn kê EMR');

        // 4.7 Danh sách bệnh nhân của bác sĩ, Lịch sử khám & Hồ sơ cá nhân bác sĩ
        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patients.index'))
            ->assertStatus(200);

        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patients.show', $this->patient->id))
            ->assertStatus(200);

        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patient.history'))
            ->assertStatus(200);

        $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.profile.edit'))
            ->assertStatus(200);
    }

    /**
     * MODULE 5: Phân hệ Quản trị Admin & Lễ tân (Dashboard, Thống kê, CRUD danh mục, Lịch trực, Xét nghiệm, Thu ngân & Chat)
     */
    public function test_module_5_admin_crud_labtest_upload_schedule_and_chat_subfunctions(): void
    {
        // 5.1 Dashboard & Thống kê
        $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.dashboard'))
            ->assertStatus(200);

        $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.statistics.index'))
            ->assertStatus(200);

        // 5.2 Quản lý danh mục: Chuyên khoa, Bác sĩ, Dịch vụ, Thuốc, Bệnh lý, Loại xét nghiệm, Bệnh nhân, Người dùng, Bệnh án
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.departments.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.doctors.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.doctors.schedule', $this->doctor->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.services.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.medicines.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.diseases.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.test-types.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.patients.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.users.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.medical-records.index'))->assertStatus(200);

        // 5.3 Quản lý lịch làm việc & chức năng con: Khóa/Mở khóa TimeSlot và Đổi sức chứa (Capacity)
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.schedules.index'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.schedules.create'))->assertStatus(200);

        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.schedules.slots.capacity', $this->timeSlotA->id), [
                'capacity' => 8,
            ])
            ->assertRedirect();
        $this->timeSlotA->refresh();
        $this->assertEquals(8, $this->timeSlotA->capacity);

        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.schedules.slots.toggle', $this->timeSlotA->id))
            ->assertRedirect();
        $this->timeSlotA->refresh();
        $this->assertEquals(TimeSlot::STATUS_LOCKED, $this->timeSlotA->status);

        // Mở khóa lại
        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.schedules.slots.toggle', $this->timeSlotA->id))
            ->assertRedirect();
        $this->timeSlotA->refresh();
        $this->assertEquals(TimeSlot::STATUS_AVAILABLE, $this->timeSlotA->status);

        // 5.4 Thu tiền tại quầy (markAsPaid)
        $appointment = app(\App\Services\AppointmentService::class)->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $this->timeSlotA->date->toDateString(),
            'time_slot_id' => $this->timeSlotA->id,
        ], $this->patientUser);

        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.appointments.payment', $appointment->id), [
                'payment_method' => 'Tiền mặt tại quầy thu ngân',
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertEquals(Appointment::PAYMENT_SUCCESS, $appointment->payment_status);

        // 5.5 Quản lý Xét nghiệm (LabTests) & Upload trả kết quả xét nghiệm
        Storage::fake('public');
        $record = MedicalRecord::firstOrCreate(
            ['appointment_id' => $appointment->id],
            ['patient_id' => $this->patient->id, 'diagnosis' => 'Khám tổng quát']
        );
        $labTest = LabTest::create([
            'medical_record_id' => $record->id,
            'department_id' => $this->department->id,
            'test_name' => 'Xét nghiệm sinh hóa máu 18 chỉ số',
            'requested_by' => $this->doctorUser->id,
            'status' => 'requested',
        ]);

        $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.lab_tests.index'))
            ->assertStatus(200)
            ->assertSee('Xét nghiệm sinh hóa máu 18 chỉ số');

        $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.lab_tests.upload', $labTest->id))
            ->assertStatus(200);

        $fakeImage = UploadedFile::fake()->image('ket-qua-xet-nghiem.jpg', 600, 800);
        $this->actingAs($this->adminUser, 'web_admin')
            ->post(route('admin.lab_tests.saveUpload', $labTest->id), [
                'image' => $fakeImage,
                'note' => 'Các chỉ số đường huyết và mỡ máu trong giới hạn bình thường.',
            ])
            ->assertRedirect(route('admin.lab_tests.index'));

        $labTest->refresh();
        $this->assertEquals('completed', $labTest->status);
        $this->assertNotNull($labTest->image);

        // 5.6 Chức năng Chat trực tuyến 2 chiều (Bệnh nhân <-> Admin)
        $this->actingAs($this->patientUser)
            ->postJson(route('chat.send'), [
                'receiver_id' => $this->adminUser->id,
                'message' => 'Xin chào phòng khám, tôi muốn hỏi về nhịn ăn trước khi xét nghiệm máu.',
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->actingAs($this->adminUser, 'web_admin')
            ->getJson(route('admin.chat.unread_count'))
            ->assertStatus(200);

        $this->actingAs($this->adminUser, 'web_admin')
            ->postJson(route('admin.chat.send'), [
                'receiver_id' => $this->patientUser->id,
                'message' => 'Chào bạn, bạn vui lòng nhịn ăn sáng từ 6-8 tiếng trước khi lấy máu nhé!',
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->actingAs($this->patientUser)
            ->getJson(route('chat.get', $this->adminUser->id))
            ->assertStatus(200);
    }

    /**
     * MODULE 6: Kiểm thử sâu các chức năng con CRUD (Thêm, Sửa, Xóa, Xem chi tiết) của Admin & Cập nhật Hồ sơ Bác sĩ
     */
    public function test_module_6_admin_deep_crud_and_doctor_profile_subfunctions(): void
    {
        // 6.1 CRUD Chuyên khoa (Departments)
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.departments.create'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->post(route('admin.departments.store'), [
            'name' => 'Khoa Thần Kinh - Đột Quỵ Test',
            'description' => 'Chuyên khoa điều trị đau đầu, rối loạn tiền đình',
            'fee' => 350000,
        ])->assertRedirect();

        $newDept = Department::where('name', 'Khoa Thần Kinh - Đột Quỵ Test')->firstOrFail();
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.departments.edit', $newDept->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->put(route('admin.departments.update', $newDept->id), [
            'name' => 'Khoa Thần Kinh Chuyên Sâu Test',
            'description' => 'Đã cập nhật mô tả',
            'fee' => 380000,
        ])->assertRedirect();
        $this->actingAs($this->adminUser, 'web_admin')->delete(route('admin.departments.destroy', $newDept->id))->assertRedirect();

        // 6.2 CRUD Dịch vụ y tế (Services)
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.services.create'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->post(route('admin.services.store'), [
            'name' => 'Gói Tầm Soát Đột Quỵ Toàn Diện Test',
            'department_id' => $this->department->id,
            'price' => 1250000,
            'description' => "Siêu âm Doppler xuyên sọ\nXét nghiệm mỡ máu",
        ])->assertRedirect();

        $newSvc = Service::where('name', 'Gói Tầm Soát Đột Quỵ Toàn Diện Test')->firstOrFail();
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.services.edit', $newSvc->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->put(route('admin.services.update', $newSvc->id), [
            'name' => 'Gói Tầm Soát Đột Quỵ VIP Test',
            'department_id' => $this->department->id,
            'price' => 1450000,
            'description' => 'Đã nâng cấp gói khám',
        ])->assertRedirect();
        $this->actingAs($this->adminUser, 'web_admin')->delete(route('admin.services.destroy', $newSvc->id))->assertRedirect();

        // 6.3 CRUD Kho thuốc (Medicines)
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.medicines.create'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->post(route('admin.medicines.store'), [
            'name' => 'Cefuroxime 500mg Test CRUD',
            'generic_name' => 'Cefuroxime axetil',
            'unit' => 'Viên',
            'price' => 8500,
            'stock' => 200,
            'min_stock' => 20,
            'dosage' => 'Ngày 2 viên chia 2 lần',
            'usage' => 'Uống sau ăn',
        ])->assertRedirect();

        $newMed = Medicine::where('name', 'Cefuroxime 500mg Test CRUD')->firstOrFail();
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.medicines.edit', $newMed->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->put(route('admin.medicines.update', $newMed->id), [
            'name' => 'Cefuroxime 500mg Updated CRUD',
            'generic_name' => 'Cefuroxime axetil',
            'unit' => 'Viên',
            'price' => 9000,
            'stock' => 250,
            'min_stock' => 20,
        ])->assertRedirect();
        $this->actingAs($this->adminUser, 'web_admin')->delete(route('admin.medicines.destroy', $newMed->id))->assertRedirect();

        // 6.4 CRUD Danh mục Loại xét nghiệm (TestTypes) & Bệnh lý (Diseases)
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.test-types.create'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->post(route('admin.test-types.store'), [
            'name' => 'Định lượng HbA1c máu tĩnh mạch Test',
            'department_id' => $this->department->id,
            'description' => 'Đánh giá đường huyết trung bình 3 tháng',
        ])->assertRedirect();

        $newTestType = TestType::where('name', 'Định lượng HbA1c máu tĩnh mạch Test')->firstOrFail();
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.test-types.edit', $newTestType->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->put(route('admin.test-types.update', $newTestType->id), [
            'name' => 'Định lượng HbA1c Chuẩn Quốc Tế Test',
            'department_id' => $this->department->id,
            'description' => 'Cập nhật mô tả xét nghiệm',
        ])->assertRedirect();
        $this->actingAs($this->adminUser, 'web_admin')->delete(route('admin.test-types.destroy', $newTestType->id))->assertRedirect();

        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.diseases.create'))->assertStatus(200);

        // 6.5 Các trang chi tiết / chỉnh sửa Bác sĩ, Bệnh nhân, Người dùng, Lịch hẹn của Admin
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.doctors.create'))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.doctors.edit', $this->doctor->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.patients.show', $this->patient->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.patients.edit', $this->patient->id))->assertStatus(200);
        $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.users.edit', $this->patientUser->id))->assertStatus(200);

        // 6.6 Bác sĩ cập nhật thông tin hồ sơ chuyên môn
        $this->actingAs($this->doctorUser, 'web_doctor')->put(route('doctor.profile.update'), [
            'name' => $this->doctorUser->name,
            'email' => $this->doctorUser->email,
            'phone' => '0912345678',
            'specialization' => $this->doctor->specialization ?? 'Nội tổng quát',
            'bio' => 'Bác sĩ chuyên khoa giàu kinh nghiệm tại 125 Thái Thịnh',
        ])->assertRedirect();

        // 6.7 Kiểm tra đăng xuất tách biệt theo guard
        $this->get('/logout?guard=web_doctor')->assertRedirect(route('doctor.login'));
    }

    /**
     * MODULE 7: KIỂM THỬ CÁC TÍNH NĂNG THÔNG MINH & CHUYÊN NGHIỆP MỚI NÂNG CẤP
     * - Tư vấn triệu chứng AI bằng câu văn tự nhiên + Cảnh báo cấp cứu Red-Flag + Đối tượng khám
     * - Gợi ý ngày có lịch trống gần nhất (next_available_dates) khi đặt lịch
     * - Hàng đợi thời gian thực & Nhắc lịch Google Calendar trên phiếu khám
     * - Chỉ số sinh tồn (Vitals + BMI) & Mẫu in đơn thuốc chuẩn Y tế trên EMR Bác sĩ
     * - Trung tâm cảnh báo vận hành & Ngữ cảnh bệnh nhân CSKH trên cổng Admin
     */
    public function test_module_7_smart_ux_and_professional_clinical_enhancements(): void
    {
        // 7.1 Tư vấn triệu chứng bằng câu tự nhiên & phát hiện dấu hiệu cấp cứu (Red Flag)
        $resAdvisor = $this->get(route('advisor.index', [
            'symptom' => 'Tôi đang bị sốt cao kèm khó thở và đau tức ngực từ hôm qua',
            'patient_group' => 'adult',
        ]));
        $resAdvisor->assertStatus(200);
        $resAdvisor->assertSee('Cảnh báo Dấu hiệu Nguy cấp');
        $resAdvisor->assertSee('khó thở');

        // 7.2 Bảng giá dịch vụ hỗ trợ sắp xếp theo giá
        $this->get(route('services.index', ['sort' => 'price_asc']))->assertStatus(200);
        $this->get(route('services.index', ['sort' => 'price_desc']))->assertStatus(200);

        // 7.3 API TimeSlots trả về gợi ý next_available_dates thông minh
        $resSlots = $this->actingAs($this->patientUser, 'web')
            ->getJson(route('appointments.time-slots', [
                'doctor_id' => $this->doctor->id,
                'date' => Carbon::tomorrow()->addDays(25)->toDateString(),
            ]));
        $resSlots->assertStatus(200)
            ->assertJsonStructure(['success', 'slots', 'next_available_dates']);

        // 7.4 Đặt lịch hẹn -> Kiểm tra tìm kiếm server-side, Mở nhanh QR & Nhắc lịch Google Calendar
        $appt = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'department_id' => $this->department->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlotA->id,
            'appointment_date' => $this->timeSlotA->date,
            'appointment_time' => $this->timeSlotA->start_time,
            'status' => Appointment::STATUS_CHECKED_IN,
            'checked_in_at' => now(),
            'payment_status' => Appointment::PAYMENT_SUCCESS,
        ]);

        $resIndex = $this->actingAs($this->patientUser, 'web')
            ->get(route('appointments.index', ['search' => $appt->appointment_code]));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Mở nhanh Mã QR');

        $resShow = $this->actingAs($this->patientUser, 'web')
            ->get(route('appointments.show', $appt->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Nhắc lịch Google');
        $resShow->assertSee('Trạng thái hàng đợi buồng khám thời gian thực');

        // 7.5 Bác sĩ xem Hàng đợi ưu tiên trên Dashboard & EMR có Chỉ số sinh tồn + Bản in Đơn thuốc chuẩn
        $resDocDash = $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.dashboard', ['date' => Carbon::parse($this->timeSlotA->date)->toDateString()]));
        $resDocDash->assertStatus(200);
        $resDocDash->assertSee('Hàng đợi ưu tiên');

        $resEmr = $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patient.record', $appt->id));
        $resEmr->assertStatus(200);
        $resEmr->assertSee('Chỉ số sinh tồn');
        $resEmr->assertSee('PHIẾU KHÁM BỆNH &amp; ĐƠN THUỐC ĐIỀU TRỊ', false);

        // 7.6 Admin Dashboard hiển thị Check-in QR 1 chạm & Chat CSKH hiển thị ngữ cảnh bệnh nhân
        $resAdminDash = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.dashboard'));
        $resAdminDash->assertStatus(200);
        $resAdminDash->assertSee('Check-in QR 1 Chạm');

        $resAdminChat = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.chat.admin'));
        $resAdminChat->assertStatus(200);
        $resAdminChat->assertSee('Mẫu phản hồi nhanh');

        // 7.7 Các trang Chuyên khoa, Hồ sơ bệnh nhân, Lịch sử EMR Bác sĩ, Kho Dược & Thống kê nâng cấp
        $resDepts = $this->get(route('departments.index'));
        $resDepts->assertStatus(200);
        $resDepts->assertSee('Gợi ý nhanh:');

        $resProfile = $this->actingAs($this->patientUser, 'web')
            ->get(route('profile.edit'));
        $resProfile->assertStatus(200);
        $resProfile->assertSee('Lượt khám hoàn tất');
        $resProfile->assertSee('Chuẩn 10 hoặc 15 ký tự');

        $resDocHist = $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patient.history'));
        $resDocHist->assertStatus(200);
        $resDocHist->assertSee('Xuất CSV bệnh án');

        $resAdminMed = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.medicines.index'));
        $resAdminMed->assertStatus(200);
        $resAdminMed->assertSee('Tổng danh mục thuốc');

        $resAdminStats = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.statistics.index'));
        $resAdminStats->assertStatus(200);
        $resAdminStats->assertSee('Xuất CSV thống kê');

        // 7.8 Hồ sơ bệnh án điện tử phía Bệnh nhân (Hiển thị Sinh hiệu, Nhắc lịch tái khám thông minh & Lịch uống thuốc 4 buổi)
        $emr = MedicalRecord::updateOrCreate(
            ['appointment_id' => $appt->id],
            [
                'patient_id' => $this->patient->id,
                'diagnosis' => 'Tăng huyết áp độ 1 (I10)',
                'description' => "[Sinh hiệu: Mạch: 78 l/p | HA: 135/85 mmHg | Nhiệt độ: 36.7 °C | SpO2: 98% | BMI: 22.4 (Bình thường)]\nĐau đầu nhẹ vùng chẩm.",
                'doctor_conclusion' => 'Uống thuốc đều đặn mỗi sáng. Hẹn tái khám vào ngày ' . now()->addDays(14)->format('d/m/Y') . ' để kiểm tra huyết áp.',
            ]
        );
        \App\Models\PrescriptionItem::create([
            'prescription_id' => $emr->id,
            'medicine_id' => $this->medicine->id,
            'quantity' => 14,
            'unit' => 'Viên',
            'usage' => 'Uống sáng 1 viên, tối 1 viên sau ăn',
        ]);

        $resPatientEmr = $this->actingAs($this->patientUser, 'web')
            ->get(route('appointments.record', $appt->id));
        $resPatientEmr->assertStatus(200);
        $resPatientEmr->assertSee('Bác sĩ hẹn lịch tái khám vào ngày');
        $resPatientEmr->assertSee('CHỈ SỐ SINH HIỆU GHI NHẬN TẠI BUỒNG KHÁM');
        $resPatientEmr->assertSee('LỊCH NHẮC UỐNG THUỐC TRONG NGÀY');

        // 7.9 Admin: Tóm tắt hồ sơ bệnh nhân & Lọc nhanh / Xuất CSV danh sách lịch hẹn
        $resAdminPatientShow = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.patients.show', $this->patient->id));
        $resAdminPatientShow->assertStatus(200);
        $resAdminPatientShow->assertSee('Tổng viện phí đã thanh toán');

        $resAdminApptIndex = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.appointments.index'));
        $resAdminApptIndex->assertStatus(200);
        $resAdminApptIndex->assertSee('Xuất CSV danh sách');

        // 7.10 Trang Giới thiệu (Quy trình 4 bước & Sơ đồ tầng), Kết quả đặt lịch, Hồ sơ bệnh nhân Bác sĩ & Quản lý EMR Admin
        $resIntro = $this->get(route('introduces.index'));
        $resIntro->assertStatus(200);
        $resIntro->assertSee('Quy trình Khám bệnh 4 Bước Thông minh');
        $resIntro->assertSee('Sơ đồ Phân khu Chuyên khoa tại 125 Thái Thịnh');

        $resSuccess = $this->actingAs($this->patientUser, 'web')
            ->get(route('appointments.success'));
        $resSuccess->assertStatus(200);
        $resSuccess->assertSee('Lưu ý chuẩn bị trước khi đến khám');

        $resDocPatientShow = $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patients.show', $this->patient->id));
        $resDocPatientShow->assertStatus(200);
        $resDocPatientShow->assertSee('Chẩn đoán gần nhất');

        $resAdminEmrIndex = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.medical-records.index'));
        $resAdminEmrIndex->assertStatus(200);
        $resAdminEmrIndex->assertSee('Xuất CSV bệnh án');

        $resAdminEmrShow = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.medical-records.show', $emr->id));
        $resAdminEmrShow->assertStatus(200);
        $resAdminEmrShow->assertSee('Chỉ số sinh hiệu ghi nhận');

        // 7.11 Tìm kiếm Mặt bệnh lý Admin, Dự toán tự động Khung giờ Ca trực & Xuất CSV Bác sĩ / Bệnh nhân
        $resAdminDiseases = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.diseases.index', ['department_id' => $this->department->id]));
        $resAdminDiseases->assertStatus(200);
        $resAdminDiseases->assertSee('Tìm kiếm & Lọc mặt bệnh lý / triệu chứng', false);

        $resAdminSchedCreate = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.schedules.create'));
        $resAdminSchedCreate->assertStatus(200);
        $resAdminSchedCreate->assertSee('Dự toán Tự động Khung Giờ');

        $resAdminDoctors = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.doctors.index'));
        $resAdminDoctors->assertStatus(200);
        $resAdminDoctors->assertSee('Xuất CSV Bác sĩ');

        $resAdminPatients = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.patients.index'));
        $resAdminPatients->assertStatus(200);
        $resAdminPatients->assertSee('Xuất CSV Bệnh nhân');
    }

    /**
     * MODULE 8: Kiểm thử các Quy tắc Nghiệp vụ Chuyên sâu (Senior BA Edge Cases):
     * - Bảo toàn booked_count khi đã Check-in (chống Overbooking)
     * - Đặt lịch cho 2 người thân khác nhau trong cùng khung giờ & chặn trùng tên chuẩn hóa ("Bé X" vs "X")
     * - Chặn trùng giờ khám bản thân giữa 2 bác sĩ khác nhau
     * - State Machine Guard & Trạng thái Vắng mặt (no_show) hoàn trả slot
     * - Tự động giải phóng giữ chỗ VNPay quá hạn 15 phút (Ghost Booking TTL)
     * - Bảng kê Viện phí đa tầng & Đồng chi trả BHYT 80%
     * - Báo nghỉ đột xuất (on_leave) & Tự động điều chuyển lịch hẹn sang bác sĩ cùng chuyên khoa
     */
    public function test_module_8_senior_ba_clinical_rules_and_edge_cases(): void
    {
        $service = app(\App\Services\AppointmentService::class);
        $baDate = now()->addDays(42)->toDateString();

        $schedule1 = WorkingSchedule::create([
            'doctor_id' => $this->doctor->id,
            'date' => $baDate,
            'day_of_week' => \Carbon\Carbon::parse($baDate)->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => WorkingSchedule::STATUS_ACTIVE,
        ]);

        $slotCap2 = TimeSlot::create([
            'working_schedule_id' => $schedule1->id,
            'doctor_id' => $this->doctor->id,
            'date' => $baDate,
            'start_time' => '08:00:00',
            'end_time' => '08:30:00',
            'capacity' => 2,
            'booked_count' => 0,
            'status' => TimeSlot::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        // 8.1 Đặt lịch cho Người thân 1 ("Bé Nguyễn Bảo An") và Check-in -> booked_count vẫn phải tính ca checked_in
        $relAppt1 = $service->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $baDate,
            'time_slot_id' => $slotCap2->id,
            'booking_for' => 'relative',
            'relative_name' => 'Bé Nguyễn Bảo An',
            'relative_relationship' => 'Con nhỏ',
        ], $this->patientUser);

        $service->updateStatus($relAppt1, Appointment::STATUS_CHECKED_IN, $this->adminUser, 'Đã check-in sớm');

        // 8.2 Cố tình đặt trùng giờ cho cùng người thân (gõ tên không có chữ "Bé ": "Nguyễn Bảo An") -> Phải bị chặn!
        $duplicateBlocked = false;
        try {
            $service->bookAppointment([
                'doctor_id' => $this->doctor->id,
                'service_id' => $this->service->id,
                'date' => $baDate,
                'time_slot_id' => $slotCap2->id,
                'booking_for' => 'relative',
                'relative_name' => 'Nguyễn Bảo An',
                'relative_relationship' => 'Con nhỏ',
            ], $this->patientUser);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $duplicateBlocked = true;
        }
        $this->assertTrue($duplicateBlocked, 'Phải chặn đặt trùng giờ cho cùng 1 người thân dù khác danh xưng Bé/Cháu');

        // 8.3 Đặt lịch cho Người thân thứ 2 ("Bé Nguyễn Bảo Châu") trên cùng tài khoản & cùng khung giờ -> Thành công & booked_count = 2 (Không bị ghi đè mất ca checked_in!)
        $relAppt2 = $service->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $baDate,
            'time_slot_id' => $slotCap2->id,
            'booking_for' => 'relative',
            'relative_name' => 'Bé Nguyễn Bảo Châu',
            'relative_relationship' => 'Con nhỏ',
        ], $this->patientUser);

        $slotCap2->refresh();
        $this->assertEquals(2, $slotCap2->booked_count, 'booked_count phải đếm cả ca đã checked_in để chống Overbooking');
        $this->assertEquals(TimeSlot::STATUS_LOCKED, $slotCap2->status);

        // 8.4 Đánh dấu Vắng mặt (STATUS_NO_SHOW) cho relAppt2 -> Giải phóng slot về 1 và chặn nhảy thẳng sang completed
        $service->updateStatus($relAppt2, Appointment::STATUS_NO_SHOW, $this->adminUser, 'Quá giờ hẹn 30 phút không đến');
        $relAppt2->refresh();
        $slotCap2->refresh();
        $this->assertEquals(Appointment::STATUS_NO_SHOW, $relAppt2->status);
        $this->assertEquals('Vắng mặt (Bỏ hẹn)', $relAppt2->status_label);
        $this->assertEquals(1, $slotCap2->booked_count);
        $this->assertEquals(TimeSlot::STATUS_AVAILABLE, $slotCap2->status);

        $illegalTransitionBlocked = false;
        try {
            $service->updateStatus($relAppt2, Appointment::STATUS_COMPLETED, $this->adminUser, 'Cố tình nhảy từ no_show sang completed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $illegalTransitionBlocked = true;
        }
        $this->assertTrue($illegalTransitionBlocked, 'State Machine Guard phải chặn chuyển trực tiếp từ no_show sang completed');

        // 8.5 Tự động giải phóng giữ chỗ VNPay treo quá 15 phút (Ghost Booking TTL Cleanup)
        $ghostAppt = $service->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $baDate,
            'time_slot_id' => $slotCap2->id,
            'booking_for' => 'self',
        ], $this->patientUser);
        $ghostAppt->transaction_ref = 'VNP-GHOST-' . $ghostAppt->id;
        $ghostAppt->created_at = now()->subMinutes(20);
        $ghostAppt->save();

        $released = $service->releaseExpiredUnpaidOnlineBookings($slotCap2->id, 15);
        $this->assertEquals(1, $released);
        $ghostAppt->refresh();
        $slotCap2->refresh();
        $this->assertEquals(Appointment::STATUS_CANCELLED, $ghostAppt->status);
        $this->assertEquals(1, $slotCap2->booked_count);

        // 8.6 Bảng kê Viện phí đa tầng & Đồng chi trả BHYT 80%
        $this->patient->update(['insurance_number' => 'DN4010123456789']);
        $selfAppt = $service->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'date' => $baDate,
            'time_slot_id' => $slotCap2->id,
            'booking_for' => 'self',
        ], $this->patientUser);

        $breakdown = $selfAppt->calculateCostBreakdown();
        $this->assertTrue($breakdown['has_bhyt']);
        $this->assertEquals(80, $breakdown['bhyt_rate_percent']);
        $this->assertEquals(round($breakdown['gross_total'] * 0.8, 0), $breakdown['bhyt_covered_amount']);

        // 8.7 Báo nghỉ đột xuất (on_leave) & Tự động điều chuyển lịch hẹn sang Bác sĩ dự phòng cùng chuyên khoa
        $backupDocUser = User::create([
            'name' => 'BS. Dự Phòng Cùng Khoa',
            'email' => 'backup.doc.' . uniqid() . '@thaithinh.vn',
            'password' => bcrypt('doctor123'),
            'role_id' => $this->doctorUser->role_id,
        ]);
        $backupDoctor = Doctor::create([
            'user_id' => $backupDocUser->id,
            'department_id' => $this->department->id,
            'specialization' => 'Tim mạch can thiệp',
            'room_number' => 'P.209',
            'is_active' => true,
        ]);
        $backupSchedule = WorkingSchedule::create([
            'doctor_id' => $backupDoctor->id,
            'date' => $baDate,
            'day_of_week' => \Carbon\Carbon::parse($baDate)->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => WorkingSchedule::STATUS_ACTIVE,
        ]);
        $backupSlot = TimeSlot::create([
            'working_schedule_id' => $backupSchedule->id,
            'doctor_id' => $backupDoctor->id,
            'date' => $baDate,
            'start_time' => '08:00:00',
            'end_time' => '08:30:00',
            'capacity' => 3,
            'booked_count' => 0,
            'status' => TimeSlot::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser, 'web_admin')
            ->patch(route('admin.schedules.leave', $schedule1->id))
            ->assertRedirect();

        $schedule1->refresh();
        $selfAppt->refresh();
        $backupSlot->refresh();
        $this->assertEquals(WorkingSchedule::STATUS_ON_LEAVE, $schedule1->status);
        $this->assertEquals($backupDoctor->id, $selfAppt->doctor_id, 'Lịch hẹn chờ khám phải được tự động điều chuyển sang bác sĩ cùng chuyên khoa');
        $this->assertEquals($backupSlot->id, $selfAppt->time_slot_id);
        $this->assertEquals(1, $backupSlot->booked_count);

        // 8.8 Kiểm tra các nâng cấp Senior UI/UX (Guest Chat Bubble, Live Booking Strip, Sticky EMR Action Bar & Admin Logout Route)
        $resHomeGuest = $this->get(route('home'));
        $resHomeGuest->assertStatus(200);
        $resHomeGuest->assertSee('chat-widget', false);

        $resCreateUi = $this->actingAs($this->patientUser, 'web')
            ->get(route('appointments.create'));
        $resCreateUi->assertStatus(200);
        $resCreateUi->assertSee('liveBookingSelectionStrip', false);

        $resDocEmrUi = $this->actingAs($this->doctorUser, 'web_doctor')
            ->get(route('doctor.patient.record', $relAppt1->id));
        $resDocEmrUi->assertStatus(200);
        $resDocEmrUi->assertSee('stickyEmrActionBar', false);
        $resDocEmrUi->assertSee('emrDirtyStatusBadge', false);

        $resAdminDashUi = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.dashboard'));
        $resAdminDashUi->assertStatus(200);
        $resAdminDashUi->assertSee(route('admin.logout'), false);
    }
}
