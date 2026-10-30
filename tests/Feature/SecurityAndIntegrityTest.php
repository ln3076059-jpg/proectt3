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
}
