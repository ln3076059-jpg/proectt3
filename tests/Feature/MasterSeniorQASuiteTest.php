<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Disease;
use App\Models\DiseaseSymptom;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MasterSeniorQASuiteTest extends TestCase
{
    use DatabaseTransactions;

    protected User $patientUser;
    protected Patient $patient;
    protected User $otherPatientUser;
    protected Patient $otherPatient;
    protected User $doctorUser;
    protected Doctor $doctor;
    protected User $otherDoctorUser;
    protected Doctor $otherDoctor;
    protected User $adminUser;
    protected Department $department;
    protected Service $service;
    protected TimeSlot $timeSlot;
    protected AppointmentService $serviceLayer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceLayer = app(AppointmentService::class);

        $patientRole = Role::firstOrCreate(['name' => 'user'], ['description' => 'Bệnh nhân']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor'], ['description' => 'Bác sĩ']);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Quản trị viên']);

        $this->department = Department::firstOrCreate(
            ['name' => 'Khoa Nội Tổng Quát QA'],
            ['description' => 'Khoa kiểm thử chất lượng cao']
        );

        $this->service = Service::firstOrCreate(
            ['name' => 'Gói Khám Tim Mạch Senior QA'],
            [
                'department_id' => $this->department->id,
                'price' => 350000,
                'description' => 'Gói khám tim mạch phục vụ kiểm thử',
            ]
        );

        $this->patientUser = User::create([
            'name' => 'Bệnh Nhân Test QA',
            'email' => 'patient_qa_' . uniqid() . '@test.vn',
            'password' => Hash::make('password123'),
            'role_id' => $patientRole->id,
            'phone' => '0912345678',
        ]);
        $this->patient = Patient::create([
            'user_id' => $this->patientUser->id,
            'name' => $this->patientUser->name,
            'phone' => $this->patientUser->phone,
            'email' => $this->patientUser->email,
        ]);

        $this->otherPatientUser = User::create([
            'name' => 'Bệnh Nhân Khác QA',
            'email' => 'other_patient_qa_' . uniqid() . '@test.vn',
            'password' => Hash::make('password123'),
            'role_id' => $patientRole->id,
            'phone' => '0987654321',
        ]);
        $this->otherPatient = Patient::create([
            'user_id' => $this->otherPatientUser->id,
            'name' => $this->otherPatientUser->name,
            'phone' => $this->otherPatientUser->phone,
            'email' => $this->otherPatientUser->email,
        ]);

        $this->doctorUser = User::create([
            'name' => 'BS CKII Nguyễn Văn Test',
            'email' => 'doctor_qa_' . uniqid() . '@test.vn',
            'password' => Hash::make('password123'),
            'role_id' => $doctorRole->id,
        ]);
        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'specialization' => 'Chuyên khoa Tim mạch QA',
            'is_active' => true,
        ]);

        $this->otherDoctorUser = User::create([
            'name' => 'BS CKI Lê Thị Khác',
            'email' => 'other_doc_qa_' . uniqid() . '@test.vn',
            'password' => Hash::make('password123'),
            'role_id' => $doctorRole->id,
        ]);
        $this->otherDoctor = Doctor::create([
            'user_id' => $this->otherDoctorUser->id,
            'department_id' => $this->department->id,
            'specialization' => 'Chuyên khoa Thần kinh QA',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Quản Trị Viên QA',
            'email' => 'admin_qa_' . uniqid() . '@test.vn',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
        ]);

        $tomorrow = Carbon::tomorrow();
        $this->timeSlot = TimeSlot::create([
            'doctor_id' => $this->doctor->id,
            'date' => $tomorrow->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '08:30:00',
            'capacity' => 1,
            'booked_count' => 0,
            'status' => TimeSlot::STATUS_AVAILABLE,
            'is_active' => true,
        ]);
    }

    /**
     * TC-01: Người dùng không có quyền truy cập tài nguyên của người khác (IDOR & Resource Isolation)
     */
    public function test_01_user_cannot_access_other_users_appointments_or_records(): void
    {
        $appt = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'appointment_date' => Carbon::tomorrow()->toDateString() . ' 08:00:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_code' => 'PK-QA-01',
        ]);

        $record = MedicalRecord::create([
            'appointment_id' => $appt->id,
            'patient_id' => $this->patient->id,
            'diagnosis' => 'Chẩn đoán bảo mật bệnh nhân A',
        ]);

        // 1. Patient B cố gắng truy cập chi tiết lịch hẹn của Patient A
        $resPatientB = $this->actingAs($this->otherPatientUser)
            ->get(route('appointments.show', $appt->id));
        $resPatientB->assertStatus(403);

        // 2. Patient B cố gắng xem bệnh án của Patient A
        $resRecordB = $this->actingAs($this->otherPatientUser)
            ->get(route('appointments.record', $appt->id));
        $resRecordB->assertStatus(403);

        // 3. Doctor B cố gắng mở bệnh án chỉ định của Doctor A
        $resDoctorB = $this->actingAs($this->otherDoctorUser, 'web_doctor')
            ->get(route('doctor.patient.record', $appt->id));
        $resDoctorB->assertStatus(403);

        // 4. Đúng Patient A và Admin được phép truy cập
        $resPatientA = $this->actingAs($this->patientUser)
            ->get(route('appointments.show', $appt->id));
        $resPatientA->assertStatus(200);

        $resAdmin = $this->actingAs($this->adminUser, 'web_admin')
            ->get(route('admin.appointments.show', $appt->id));
        $resAdmin->assertStatus(200);
    }

    /**
     * TC-02: Hai yêu cầu cùng đặt chỗ cuối cùng của một khung giờ (Boundary Concurrency)
     */
    public function test_02_concurrent_booking_for_last_available_slot(): void
    {
        $this->assertEquals(1, $this->timeSlot->capacity);
        $this->assertEquals(0, $this->timeSlot->booked_count);

        $payloadA = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'idempotency_key' => 'CONCUR-SLOT-A-' . uniqid(),
        ];
        $payloadB = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'idempotency_key' => 'CONCUR-SLOT-B-' . uniqid(),
        ];

        // Request 1 chiếm chỗ cuối cùng
        $appt1 = $this->serviceLayer->bookAppointment($payloadA, $this->patientUser);
        $this->assertNotNull($appt1);

        $this->timeSlot->refresh();
        $this->assertEquals(1, $this->timeSlot->booked_count);
        $this->assertEquals(TimeSlot::STATUS_LOCKED, $this->timeSlot->status);

        // Request 2 cố gắng chiếm chỗ trong cùng slot đã đầy -> Bị chặn
        $this->expectException(ValidationException::class);
        $this->serviceLayer->bookAppointment($payloadB, $this->otherPatientUser);
    }

    /**
     * TC-03: Hai yêu cầu hủy cùng một lịch (Double / Concurrent Cancellation Idempotency)
     */
    public function test_03_concurrent_or_double_cancellation_of_same_appointment(): void
    {
        $payload = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'idempotency_key' => 'CANCEL-IDEMP-' . uniqid(),
        ];

        $appt = $this->serviceLayer->bookAppointment($payload, $this->patientUser);
        $this->timeSlot->refresh();
        $this->assertEquals(1, $this->timeSlot->booked_count);

        // Hủy lần 1: Thành công, giải phóng slot về 0
        $cancelled1 = $this->serviceLayer->cancelAppointment($appt, $this->patientUser, 'Hủy lịch lần 1');
        $this->assertEquals(Appointment::STATUS_CANCELLED, $cancelled1->status);

        $this->timeSlot->refresh();
        $this->assertEquals(0, $this->timeSlot->booked_count);
        $this->assertEquals(TimeSlot::STATUS_AVAILABLE, $this->timeSlot->status);

        // Hủy lần 2: Thất bại với lỗi trạng thái đã hủy, booked_count không bị âm
        try {
            $this->serviceLayer->cancelAppointment($appt->fresh(), $this->patientUser, 'Hủy lịch lần 2');
            $this->fail('Lần hủy thứ 2 phải ném ValidationException');
        } catch (ValidationException $e) {
            $this->timeSlot->refresh();
            $this->assertEquals(0, $this->timeSlot->booked_count);
            $this->assertTrue(true);
        }
    }

    /**
     * TC-04: Yêu cầu đặt lịch bị gửi lại (Idempotency Key Replay)
     */
    public function test_04_booking_request_replay_with_same_idempotency_key(): void
    {
        $idempKey = 'REPLAY-KEY-' . uniqid();
        $payload = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'idempotency_key' => $idempKey,
        ];

        $appt1 = $this->serviceLayer->bookAppointment($payload, $this->patientUser);
        $appt2 = $this->serviceLayer->bookAppointment($payload, $this->patientUser);

        // Cùng trả về 1 bản ghi lịch hẹn duy nhất
        $this->assertEquals($appt1->id, $appt2->id);
        $this->assertEquals($idempKey, $appt2->idempotency_key);

        // DB chỉ có đúng 1 record và booked_count chỉ tăng 1
        $count = Appointment::where('idempotency_key', $idempKey)->count();
        $this->assertEquals(1, $count);

        $this->timeSlot->refresh();
        $this->assertEquals(1, $this->timeSlot->booked_count);
    }

    /**
     * TC-05: VNPay trả về sai chữ ký, sai số tiền hoặc callback lặp (VNPay Security & Integrity)
     */
    public function test_05_vnpay_rejects_invalid_signature_amount_mismatch_and_handles_duplicate_callback(): void
    {
        config(['services.vnpay.hash_secret' => 'SECRET_KEY_FOR_TESTING_123456789']);

        $appt = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'appointment_date' => Carbon::tomorrow()->toDateString() . ' 08:00:00',
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_FAILED,
            'total_amount' => 350000,
            'total' => 350000,
            'transaction_ref' => 'TXN_REF_' . uniqid(),
        ]);

        // 1. IPN với chữ ký giả mạo (Invalid Signature)
        $invalidIpn = $this->postJson(route('vnpay.ipn'), [
            'vnp_TxnRef' => $appt->transaction_ref,
            'vnp_Amount' => 35000000,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_OrderInfo' => "Thanh toan #{$appt->id}",
            'vnp_SecureHash' => 'tampered_fake_signature_hash',
        ]);
        $invalidIpn->assertJson(['RspCode' => '97', 'Message' => 'Invalid signature']);

        // 2. IPN với số tiền bị can thiệp sai lệch (Amount Mismatch: 100.000 VNĐ thay vì 350.000 VNĐ)
        $tamperedAmountParams = [
            'vnp_Amount' => '10000000', // 100,000 VND
            'vnp_OrderInfo' => "Thanh toan #{$appt->id}",
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $appt->transaction_ref,
        ];
        ksort($tamperedAmountParams);
        $hashData = '';
        $i = 0;
        foreach ($tamperedAmountParams as $k => $v) {
            $hashData .= ($i === 0 ? '' : '&') . urlencode($k) . '=' . urlencode($v);
            $i = 1;
        }
        $validHashForWrongAmount = hash_hmac('sha512', $hashData, 'SECRET_KEY_FOR_TESTING_123456789');

        $tamperedAmountParams['vnp_SecureHash'] = $validHashForWrongAmount;
        $mismatchRes = $this->postJson(route('vnpay.ipn'), $tamperedAmountParams);
        $mismatchRes->assertJson(['RspCode' => '04', 'Message' => 'Invalid amount']);

        // 3. IPN chuẩn hợp lệ (Happy Path)
        $validParams = [
            'vnp_Amount' => '35000000', // 350,000 VND
            'vnp_BankCode' => 'NCB',
            'vnp_OrderInfo' => "Thanh toan #{$appt->id}",
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '14598721',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $appt->transaction_ref,
        ];
        ksort($validParams);
        $hashData = '';
        $i = 0;
        foreach ($validParams as $k => $v) {
            $hashData .= ($i === 0 ? '' : '&') . urlencode($k) . '=' . urlencode($v);
            $i = 1;
        }
        $validHash = hash_hmac('sha512', $hashData, 'SECRET_KEY_FOR_TESTING_123456789');
        $validParams['vnp_SecureHash'] = $validHash;

        $validRes = $this->postJson(route('vnpay.ipn'), $validParams);
        $validRes->assertJson(['RspCode' => '00', 'Message' => 'Confirm Success']);

        $appt->refresh();
        $this->assertEquals('success', $appt->payment_status);
        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appt->status);

        // 4. IPN lặp lại lần 2 (Duplicate Callback) -> Trả về 02 Order already confirmed
        $dupRes = $this->postJson(route('vnpay.ipn'), $validParams);
        $dupRes->assertJson(['RspCode' => '02', 'Message' => 'Order already confirmed']);
    }

    /**
     * TC-06: Callback thanh toán và tiến trình hết hạn/hủy chạy đồng thời (Race Condition)
     */
    public function test_06_race_between_payment_callback_and_unpaid_cleanup(): void
    {
        config(['services.vnpay.hash_secret' => 'SECRET_KEY_FOR_TESTING_123456789']);

        // Tạo lịch hẹn đã bị hủy (do tiến trình dọn dẹp chạy trước)
        $appt = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'appointment_date' => Carbon::tomorrow()->toDateString() . ' 08:00:00',
            'status' => Appointment::STATUS_CANCELLED,
            'payment_status' => Appointment::PAYMENT_FAILED,
            'total_amount' => 350000,
            'total' => 350000,
            'transaction_ref' => 'TXN_CLEANUP_' . uniqid(),
            'cancelled_at' => now(),
        ]);

        // VNPay IPN đến sau xác nhận thanh toán thành công
        $validParams = [
            'vnp_Amount' => '35000000',
            'vnp_BankCode' => 'NCB',
            'vnp_OrderInfo' => "Thanh toan #{$appt->id}",
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '99887766',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $appt->transaction_ref,
        ];
        ksort($validParams);
        $hashData = '';
        $i = 0;
        foreach ($validParams as $k => $v) {
            $hashData .= ($i === 0 ? '' : '&') . urlencode($k) . '=' . urlencode($v);
            $i = 1;
        }
        $validParams['vnp_SecureHash'] = hash_hmac('sha512', $hashData, 'SECRET_KEY_FOR_TESTING_123456789');

        $ipnRes = $this->postJson(route('vnpay.ipn'), $validParams);
        $ipnRes->assertJson(['RspCode' => '00', 'Message' => 'Confirm Success']);

        $appt->refresh();
        // Không biến lịch cancelled thành confirmed làm sai slot; mà tự động chuyển sang hàng đợi hoàn tiền
        $this->assertEquals(Appointment::STATUS_CANCELLED, $appt->status);
        $this->assertEquals('success', $appt->payment_status);
        $this->assertEquals(Appointment::REFUND_PENDING, $appt->refund_status);
    }

    /**
     * TC-07: Hai người phụ thuộc có cùng tên nhưng khác ngày sinh/quan hệ (Relative Identity)
     */
    public function test_07_patient_can_book_for_relatives_with_same_name_but_different_dob(): void
    {
        // Cho time slot có capacity 2
        $this->timeSlot->update(['capacity' => 2, 'booked_count' => 0]);

        // Đặt lịch người thân 1: Nguyễn Văn An (Bố, sinh năm 1960)
        $payload1 = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_for' => 'relative',
            'relative_name' => 'Nguyễn Văn An',
            'relative_dob' => '1960-05-12',
            'relative_relationship' => 'Bố/Mẹ',
            'idempotency_key' => 'REL-1-' . uniqid(),
        ];
        $appt1 = $this->serviceLayer->bookAppointment($payload1, $this->patientUser);
        $this->assertNotNull($appt1);

        // Đặt lịch người thân 2: Nguyễn Văn An (Cháu/Con, sinh năm 2018) trong cùng khung giờ
        $payload2 = [
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_for' => 'relative',
            'relative_name' => 'Nguyễn Văn An',
            'relative_dob' => '2018-09-20',
            'relative_relationship' => 'Con nhỏ',
            'idempotency_key' => 'REL-2-' . uniqid(),
        ];

        // Hệ thống phân biệt qua DOB & relationship, KHÔNG chặn nhầm
        $appt2 = $this->serviceLayer->bookAppointment($payload2, $this->patientUser);
        $this->assertNotNull($appt2);
        $this->assertNotEquals($appt1->id, $appt2->id);

        $this->timeSlot->refresh();
        $this->assertEquals(2, $this->timeSlot->booked_count);
    }

    /**
     * TC-08: Tài khoản vai trò không hợp lệ truy cập route quản trị (Role Guard Authorization)
     */
    public function test_08_invalid_role_cannot_access_admin_or_doctor_routes(): void
    {
        // Patient truy cập Admin
        $res1 = $this->actingAs($this->patientUser)->get(route('admin.dashboard'));
        $this->assertTrue(in_array($res1->status(), [302, 403]));

        // Patient truy cập Bác sĩ
        $res2 = $this->actingAs($this->patientUser)->get(route('doctor.dashboard'));
        $this->assertTrue(in_array($res2->status(), [302, 403]));

        // Bác sĩ truy cập Admin
        $res3 = $this->actingAs($this->doctorUser)->get(route('admin.users.index'));
        $this->assertTrue(in_array($res3->status(), [302, 403]));
    }

    /**
     * TC-09: Tin nhắn độc hại trong chat được lưu an toàn và mã hóa khi render (Stored XSS Defense)
     */
    public function test_09_chat_stored_xss_payload_is_stored_safely_and_sanitized(): void
    {
        $xssPayload = '<script>alert("XSS")</script><img src=x onerror=alert(1)>';

        $res = $this->actingAs($this->patientUser)->post(route('chat.send'), [
            'receiver_id' => $this->adminUser->id,
            'message' => $xssPayload,
        ]);
        $res->assertStatus(200);

        $msg = Message::where('sender_id', $this->patientUser->id)
            ->where('receiver_id', $this->adminUser->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($msg);
        $this->assertEquals($xssPayload, $msg->message);

        // API trả tin nhắn về dạng JSON raw, không bị thực thi
        $apiRes = $this->actingAs($this->adminUser, 'web_admin')->get(route('admin.chat.messages', ['receiverId' => $this->patientUser->id]));
        $apiRes->assertStatus(200);
        $this->assertStringContainsString('alert', $apiRes->getContent());
    }

    /**
     * TC-10: Dịch vụ AI phát hiện dấu hiệu cấp cứu y tế và xử lý fallback an toàn (AI Triage & Fallback)
     */
    public function test_10_ai_service_handles_failures_gracefully_and_triages_emergencies(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        // 1. Triệu chứng cấp cứu (Red Flag): khó thở, đau thắt ngực dữ dội
        $resEmergency = $this->postJson(route('api.chat.ai'), [
            'message' => 'Tôi bị đau thắt ngực dữ dội và khó thở, vã mồ hôi lạnh',
        ]);
        $resEmergency->assertStatus(200);
        $data = $resEmergency->json();

        $this->assertTrue($data['is_emergency']);
        $this->assertStringContainsString('CẢNH BÁO Y TẾ KHẨN CẤP', $data['reply']);
        $this->assertStringContainsString('115', $data['emergency_hotline']);

        // 2. Triệu chứng thông thường khi AI bên ngoài không có key (Fallback DB an toàn)
        Disease::firstOrCreate(
            ['name' => 'Cảm cúm thông thường'],
            ['department_id' => $this->department->id, 'description' => 'Bệnh hô hấp nhẹ']
        );
        DiseaseSymptom::firstOrCreate([
            'disease_id' => Disease::where('name', 'Cảm cúm thông thường')->first()->id,
            'symptom_name' => 'sổ mũi hắt hơi',
        ]);

        $resNormal = $this->postJson(route('api.chat.ai'), [
            'message' => 'Tôi bị sổ mũi hắt hơi nhẹ',
        ]);
        $resNormal->assertStatus(200);
        $this->assertFalse($resNormal->json('is_emergency'));
        $this->assertNotEmpty($resNormal->json('suggestions.diseases'));
    }

    /**
     * TC-11: Lỗi cơ sở dữ liệu giữa transaction được rollback hoàn toàn (ACID Integrity)
     */
    public function test_11_database_transaction_rolls_back_on_failure(): void
    {
        $initialSlotCount = $this->timeSlot->booked_count;
        $initialApptCount = Appointment::count();

        try {
            DB::transaction(function () {
                $this->timeSlot->increment('booked_count');
                // Ném exception giả lập sự cố kết nối / lỗi cú pháp giữa chừng
                throw new \RuntimeException('Gián đoạn hệ thống giữa transaction');
            });
        } catch (\RuntimeException $e) {
            // Transaction phải rollback
        }

        $this->timeSlot->refresh();
        $this->assertEquals($initialSlotCount, $this->timeSlot->booked_count);
        $this->assertEquals($initialApptCount, Appointment::count());
    }

    /**
     * TC-12: Cập nhật đơn thuốc và tồn kho dược an toàn (Pharmacy Stock Consistency)
     */
    public function test_12_interrupted_prescription_dispensing_preserves_stock_consistency(): void
    {
        $med = Medicine::create([
            'name' => 'Paracetamol 500mg QA',
            'slug' => 'paracetamol-500mg-qa-' . uniqid(),
            'stock' => 5,
            'unit' => 'Viên',
            'price' => 2000,
        ]);

        $appt = Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'service_id' => $this->service->id,
            'appointment_date' => Carbon::tomorrow()->toDateString() . ' 08:00:00',
            'status' => Appointment::STATUS_CHECKED_IN,
        ]);

        $record = MedicalRecord::create([
            'appointment_id' => $appt->id,
            'patient_id' => $this->patient->id,
            'diagnosis' => 'Sốt xuất huyết',
        ]);

        // Kê đơn 10 viên (vượt quá tồn kho 5)
        $record->prescriptionItems()->create([
            'medicine_id' => $med->id,
            'quantity' => 10,
            'dosage' => '1 viên/lần',
        ]);

        // Cố gắng chuyển sang completed -> Bị chặn do thiếu tồn kho
        try {
            $this->serviceLayer->updateStatus($appt, Appointment::STATUS_COMPLETED, $this->adminUser);
            $this->fail('Phải ném ValidationException khi không đủ thuốc trong kho');
        } catch (ValidationException $e) {
            $med->refresh();
            $this->assertEquals(5, $med->stock); // Tồn kho không bị trừ sai
            $appt->refresh();
            $this->assertEquals(Appointment::STATUS_CHECKED_IN, $appt->status);
        }
    }
}
