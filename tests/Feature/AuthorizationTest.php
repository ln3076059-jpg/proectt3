<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctor1User;
    protected User $doctor2User;
    protected Doctor $doctor1;
    protected Doctor $doctor2;
    protected User $patientAUser;
    protected User $patientBUser;
    protected Patient $patientA;
    protected Patient $patientB;
    protected Service $service;
    protected Appointment $appointmentA;
    protected Appointment $appointmentB;

    protected function setUp(): void
    {
        parent::setUp();

        if (Doctor::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed');
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $patientRole = Role::firstOrCreate(['name' => 'patient']);

        $this->adminUser = User::where('email', 'admin@thaithinh.vn')->first()
            ?? User::factory()->create(['role_id' => $adminRole->id]);

        $this->doctor1 = Doctor::first();
        $this->doctor1User = $this->doctor1->user;

        $this->doctor2 = Doctor::skip(1)->first() ?? $this->doctor1;
        $this->doctor2User = $this->doctor2->user;

        $this->patientA = Patient::first();
        $this->patientAUser = $this->patientA->user;

        $this->patientB = Patient::skip(1)->first();
        $this->patientBUser = $this->patientB->user;

        $this->service = Service::first();

        // Tạo lịch hẹn cho Patient A với Doctor 1
        $this->appointmentA = Appointment::create([
            'appointment_code' => Appointment::generateCode(),
            'patient_id' => $this->patientA->id,
            'doctor_id' => $this->doctor1->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->addDays(2),
            'medical_examination' => 'Ca sáng (07:30 - 11:30)',
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_FAILED,
            'note' => 'Khám tổng quát A',
        ]);

        // Tạo lịch hẹn cho Patient B với Doctor 2
        $this->appointmentB = Appointment::create([
            'appointment_code' => Appointment::generateCode(),
            'patient_id' => $this->patientB->id,
            'doctor_id' => $this->doctor2->id,
            'service_id' => $this->service->id,
            'appointment_date' => now()->addDays(2),
            'medical_examination' => 'Ca chiều (13:00 - 17:00)',
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_FAILED,
            'note' => 'Khám tổng quát B',
        ]);
    }

    /**
     * Test 1: Bệnh nhân A KHÔNG THỂ xem chi tiết lịch hẹn của Bệnh nhân B (403 Forbidden)
     */
    public function test_patient_a_cannot_view_patient_b_appointment(): void
    {
        $response = $this->actingAs($this->patientAUser)
            ->get(route('appointments.show', $this->appointmentB->id));

        $response->assertStatus(403);
    }

    /**
     * Test 2: Bệnh nhân A KHÔNG THỂ hủy lịch hẹn của Bệnh nhân B (403 Forbidden)
     */
    public function test_patient_a_cannot_cancel_patient_b_appointment(): void
    {
        $response = $this->actingAs($this->patientAUser)
            ->patch(route('appointments.cancel', $this->appointmentB->id), [
                'cancellation_reason' => 'Cố tình hủy lịch người khác',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test 3: Bác sĩ 1 KHÔNG THỂ truy cập hồ sơ bệnh án của lịch hẹn thuộc Bác sĩ 2 (403 Forbidden)
     */
    public function test_doctor_cannot_view_appointment_record_assigned_to_another_doctor(): void
    {
        if ($this->doctor1->id === $this->doctor2->id) {
            $this->markTestSkipped('Cần tối thiểu 2 bác sĩ khác nhau để kiểm thử.');
        }

        $response = $this->actingAs($this->doctor1User, 'web_doctor')
            ->get(route('doctor.patient.record', $this->appointmentB->id));

        $response->assertStatus(403);
    }

    /**
     * Test 4: Bệnh nhân KHÔNG THỂ truy cập Dashboard quản trị (phải bị từ chối chuyển hướng)
     */
    public function test_patient_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->patientAUser)
            ->get(route('admin.dashboard'));

        // Guard auth:web_admin sẽ từ chối bệnh nhân và chuyển hướng về trang đăng nhập
        $response->assertRedirect();
        $this->assertNotEquals(200, $response->status());
    }

    /**
     * Test 5: Người dùng chưa đăng nhập KHÔNG THỂ đặt lịch hẹn (Chuyển hướng về login)
     */
    public function test_unauthenticated_user_cannot_access_booking(): void
    {
        $response = $this->get(route('appointments.create'));
        $response->assertRedirect(route('login'));
    }
}
