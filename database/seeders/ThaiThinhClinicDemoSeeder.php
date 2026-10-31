<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\WorkingSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ThaiThinhClinicDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $patientRole = Role::firstOrCreate(['name' => 'patient']);

        // 2. Admin User
        User::firstOrCreate(
            ['email' => 'admin@thaithinh.vn'],
            [
                'name' => 'Quản trị viên Thái Thịnh',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        // 3. Departments (F03, F09)
        $departmentsData = [
            [
                'name' => 'Khoa Tim mạch',
                'description' => 'Chuyên chẩn đoán và điều trị các bệnh lý tim mạch, mạch vành, tăng huyết áp bằng kỹ thuật cao.',
                'is_active' => true,
            ],
            [
                'name' => 'Khoa Tai Mũi Họng',
                'description' => 'Khám và điều trị các bệnh lý tai mũi họng với hệ thống nội soi ống mềm hiện đại không đau.',
                'is_active' => true,
            ],
            [
                'name' => 'Khoa Nhi',
                'description' => 'Chăm sóc sức khỏe toàn diện cho trẻ em, theo dõi phát triển thể chất và tiêm chủng phòng bệnh.',
                'is_active' => true,
            ],
            [
                'name' => 'Khoa Nội tổng quát',
                'description' => 'Tầm soát, phát hiện sớm và điều trị các bệnh lý mạn tính: đái tháo đường, tiêu hóa, gan mật.',
                'is_active' => true,
            ],
            [
                'name' => 'Khoa Sản phụ khoa',
                'description' => 'Khám thai định kỳ, siêu âm 4D sàng lọc dị tật và điều trị chuyên sâu bệnh lý phụ khoa.',
                'is_active' => true,
            ],
        ];

        $departments = [];
        foreach ($departmentsData as $d) {
            $departments[$d['name']] = Department::firstOrCreate(['name' => $d['name']], $d);
        }

        // 4. Services (F05)
        $servicesData = [
            [
                'name' => 'Gói khám tim mạch chuyên sâu',
                'department_id' => $departments['Khoa Tim mạch']->id,
                'price' => 500000,
                'description' => 'Khám lâm sàng tim mạch, đo điện tâm đồ ECG và tư vấn chuyên gia.',
            ],
            [
                'name' => 'Điện tâm đồ ECG & Tư vấn chuyên gia',
                'department_id' => $departments['Khoa Tim mạch']->id,
                'price' => 250000,
                'description' => 'Đo điện tâm đồ thế hệ mới và đọc kết quả bởi bác sĩ chuyên khoa tim mạch.',
            ],
            [
                'name' => 'Khám nội soi tai mũi họng kỹ thuật cao',
                'department_id' => $departments['Khoa Tai Mũi Họng']->id,
                'price' => 300000,
                'description' => 'Nội soi ống mềm không đau phát hiện sớm viêm tai giữa, viêm xoang, hạt dây thanh.',
            ],
            [
                'name' => 'Gói khám sức khỏe nhi toàn diện',
                'department_id' => $departments['Khoa Nhi']->id,
                'price' => 350000,
                'description' => 'Khám tổng quát, đánh giá cột mốc vận động, tư vấn dinh dưỡng và phòng ngừa còi xương.',
            ],
            [
                'name' => 'Gói tầm soát sức khỏe tổng quát',
                'department_id' => $departments['Khoa Nội tổng quát']->id,
                'price' => 600000,
                'description' => 'Kiểm tra toàn diện các cơ quan nội tạng, chức năng gan thận, đường huyết và huyết áp.',
            ],
            [
                'name' => 'Quản lý tăng huyết áp & Tiểu đường',
                'department_id' => $departments['Khoa Nội tổng quát']->id,
                'price' => 400000,
                'description' => 'Khám chuyên đề đánh giá hiệu quả thuốc và điều chỉnh phác đồ kiểm soát đường huyết.',
            ],
            [
                'name' => 'Khám thai và siêu âm định kỳ',
                'department_id' => $departments['Khoa Sản phụ khoa']->id,
                'price' => 350000,
                'description' => 'Theo dõi sự phát triển thai nhi, đo tim thai và tư vấn dinh dưỡng thai kỳ.',
            ],
        ];

        $services = [];
        foreach ($servicesData as $s) {
            $services[] = Service::firstOrCreate(['name' => $s['name']], $s);
        }

        // 5. Doctors (F03, F09)
        $doctorsData = [
            [
                'name' => 'BS. CKII Nguyễn Văn Hùng',
                'email' => 'hung.nguyen@thaithinh.vn',
                'phone' => '0912111001',
                'department' => 'Khoa Tim mạch',
                'specialization' => 'Bác sĩ Chuyên khoa II Tim mạch',
                'license_number' => '012456/HN-CCHN',
                'room_number' => 'P.201 - Tầng 2',
                'description' => 'Hơn 20 năm kinh nghiệm điều trị bệnh mạch vành, rối loạn nhịp tim tại các bệnh viện tuyến trung ương.',
            ],
            [
                'name' => 'ThS. BS Trần Thị Mai',
                'email' => 'mai.tran@thaithinh.vn',
                'phone' => '0912111002',
                'department' => 'Khoa Tai Mũi Họng',
                'specialization' => 'Thạc sĩ Tai Mũi Họng',
                'license_number' => '013789/HN-CCHN',
                'room_number' => 'P.205 - Tầng 2',
                'description' => 'Chuyên gia nội soi chẩn đoán sớm các bệnh lý vùng họng thanh quản và viêm xoang mũi.',
            ],
            [
                'name' => 'BSCK I Lê Hoàng Nam',
                'email' => 'nam.le@thaithinh.vn',
                'phone' => '0912111003',
                'department' => 'Khoa Nhi',
                'specialization' => 'Bác sĩ Chuyên khoa I Nhi khoa',
                'license_number' => '015678/HN-CCHN',
                'room_number' => 'P.302 - Tầng 3',
                'description' => '15 năm kinh nghiệm khám chữa bệnh hô hấp nhi, dị ứng và tư vấn tiêm chủng an toàn.',
            ],
            [
                'name' => 'BSCK II Phạm Thị Thu Hương',
                'email' => 'huong.pham@thaithinh.vn',
                'phone' => '0912111004',
                'department' => 'Khoa Nội tổng quát',
                'specialization' => 'Bác sĩ Chuyên khoa II Nội khoa',
                'license_number' => '019234/HN-CCHN',
                'room_number' => 'P.102 - Tầng 1',
                'description' => 'Nguyên Trưởng khoa Khám bệnh, chuyên gia điều trị đái tháo đường và rối loạn chuyển hóa.',
            ],
        ];

        $doctors = [];
        foreach ($doctorsData as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
                    'password' => Hash::make('doctor123'),
                    'role_id' => $doctorRole->id,
                    'phone' => $d['phone'],
                    'email_verified_at' => now(),
                ]
            );

            $doctor = Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $departments[$d['department']]->id,
                    'specialization' => $d['specialization'],
                    'license_number' => $d['license_number'],
                    'room_number' => $d['room_number'],
                    'description' => $d['description'],
                    'is_active' => true,
                    'birth_date' => Carbon::now()->subYears(45)->toDateString(),
                ]
            );

            $doctors[] = $doctor;
        }

        // 6. Patients (F01, F08)
        $patientsData = [
            [
                'name' => 'Bùi Tuấn Anh',
                'email' => 'patient1@thaithinh.vn',
                'phone' => '0987654321',
                'birthdate' => '1995-04-12',
                'gender' => 'Nam',
                'address' => 'Số 15 phố Thái Thịnh, Đống Đa, Hà Nội',
            ],
            [
                'name' => 'Vũ Thị Hồng Nhung',
                'email' => 'patient2@thaithinh.vn',
                'phone' => '0912345678',
                'birthdate' => '1998-08-20',
                'gender' => 'Nữ',
                'address' => 'Số 88 phố Giảng Võ, Ba Đình, Hà Nội',
            ],
            [
                'name' => 'Nguyễn Minh Quang',
                'email' => 'patient3@thaithinh.vn',
                'phone' => '0934567890',
                'birthdate' => '1988-11-05',
                'gender' => 'Nam',
                'address' => 'Số 42 Cầu Giấy, Cầu Giấy, Hà Nội',
            ],
            [
                'name' => 'Lê Thu Trang',
                'email' => 'patient4@thaithinh.vn',
                'phone' => '0976543210',
                'birthdate' => '2000-01-15',
                'gender' => 'Nữ',
                'address' => 'Số 209 Nguyễn Trãi, Thanh Xuân, Hà Nội',
            ],
            [
                'name' => 'Hoàng Đức Trọng',
                'email' => 'patient5@thaithinh.vn',
                'phone' => '0908765432',
                'birthdate' => '1975-06-30',
                'gender' => 'Nam',
                'address' => 'Số 54 phố Huế, Hai Bà Trưng, Hà Nội',
            ],
            [
                'name' => 'Đỗ Phương Linh',
                'email' => 'patient6@thaithinh.vn',
                'phone' => '0965432109',
                'birthdate' => '1992-09-18',
                'gender' => 'Nữ',
                'address' => 'Số 12 phố Định Công, Hoàng Mai, Hà Nội',
            ],
        ];

        $patients = [];
        foreach ($patientsData as $p) {
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make('demo123'),
                    'role_id' => $patientRole->id,
                    'phone' => $p['phone'],
                    'email_verified_at' => now(),
                ]
            );

            $patient = Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'birthdate' => $p['birthdate'],
                    'gender' => $p['gender'],
                    'address' => $p['address'],
                    'phone' => $p['phone'],
                ]
            );

            $patients[] = $patient;
        }

        // 7. Working Schedules & Time Slots (7 days for each doctor)
        $startDate = Carbon::today();
        foreach ($doctors as $idx => $doctor) {
            for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                $date = $startDate->copy()->addDays($dayOffset)->toDateString();

                $schedule = WorkingSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'date' => $date,
                    ],
                    [
                        'day_of_week' => Carbon::parse($date)->dayOfWeek,
                        'start_time' => '07:30:00',
                        'end_time' => '17:00:00',
                        'status' => WorkingSchedule::STATUS_ACTIVE,
                        'note' => 'Ca làm việc thường quy tại phòng khám',
                    ]
                );

                // Tạo khung giờ nếu chưa có
                if ($schedule->timeSlots()->count() === 0) {
                    // Ca sáng: 07:30 -> 11:30 (30 phút / slot)
                    // Ca chiều: 13:30 -> 17:00 (30 phút / slot)
                    $slotsConfig = [
                        ['07:30:00', '08:00:00', 1],
                        ['08:00:00', '08:30:00', 5],
                        ['08:30:00', '09:00:00', 5],
                        ['09:00:00', '09:30:00', 1],
                        ['09:30:00', '10:00:00', 5],
                        ['10:00:00', '10:30:00', 5],
                        ['10:30:00', '11:00:00', 1],
                        ['11:00:00', '11:30:00', 1],
                        ['13:30:00', '14:00:00', 1],
                        ['14:00:00', '14:30:00', 5],
                        ['14:30:00', '15:00:00', 5],
                        ['15:00:00', '15:30:00', 1],
                        ['15:30:00', '16:00:00', 5],
                        ['16:00:00', '16:30:00', 5],
                        ['16:30:00', '17:00:00', 1],
                    ];

                    foreach ($slotsConfig as $sc) {
                        TimeSlot::create([
                            'working_schedule_id' => $schedule->id,
                            'doctor_id' => $doctor->id,
                            'date' => $date,
                            'start_time' => $sc[0],
                            'end_time' => $sc[1],
                            'capacity' => $sc[2],
                            'booked_count' => 0,
                            'status' => TimeSlot::STATUS_AVAILABLE,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

        // 8. Appointments & Audit Histories (F05, F06, F11, F12)
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Appointment 1: Confirmed (Today)
        $slot1 = TimeSlot::where('date', $today)->where('capacity', 5)->first();
        if ($slot1) {
            $app1 = Appointment::create([
                'appointment_code' => Appointment::generateCode(),
                'patient_id' => $patients[0]->id,
                'doctor_id' => $slot1->doctor_id,
                'service_id' => $services[0]->id,
                'time_slot_id' => $slot1->id,
                'appointment_date' => $today . ' ' . $slot1->start_time,
                'medical_examination' => 'Ca sáng (07:30 - 11:30)',
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => Appointment::PAYMENT_SUCCESS,
                'paid_at' => now(),
                'note' => 'Bệnh nhân cảm thấy hồi hộp, đau tức nhẹ ngực trái khi gắng sức.',
            ]);
            $slot1->increment('booked_count');

            AppointmentStatusHistory::create([
                'appointment_id' => $app1->id,
                'from_status' => null,
                'to_status' => Appointment::STATUS_PENDING,
                'changed_by_user_id' => $patients[0]->user_id,
                'action_role' => 'patient',
                'note' => 'Bệnh nhân đặt lịch trực tuyến qua hệ thống',
            ]);
            AppointmentStatusHistory::create([
                'appointment_id' => $app1->id,
                'from_status' => Appointment::STATUS_PENDING,
                'to_status' => Appointment::STATUS_CONFIRMED,
                'changed_by_user_id' => User::where('email', 'admin@thaithinh.vn')->first()->id,
                'action_role' => 'admin',
                'note' => 'Admin xác nhận lịch hẹn hợp lệ và đã thanh toán',
            ]);
        }

        // Appointment 2: Completed with Medical Record
        $yesterday = Carbon::yesterday()->toDateString();
        $app2 = Appointment::create([
            'appointment_code' => Appointment::generateCode(),
            'patient_id' => $patients[1]->id,
            'doctor_id' => $doctors[1]->id,
            'service_id' => $services[2]->id,
            'appointment_date' => $yesterday . ' 08:30:00',
            'medical_examination' => 'Ca sáng (07:30 - 11:30)',
            'status' => Appointment::STATUS_COMPLETED,
            'payment_status' => Appointment::PAYMENT_SUCCESS,
            'paid_at' => Carbon::yesterday()->subHours(2),
            'note' => 'Bệnh nhân nghẹt mũi, khàn giọng 3 ngày.',
        ]);

        MedicalRecord::create([
            'appointment_id' => $app2->id,
            'patient_id' => $patients[1]->id,
            'diagnosis' => 'Viêm mũi xoang cấp, viêm thanh quản thể xuất tiết',
            'doctor_conclusion' => 'Bệnh nhân đáp ứng tốt, cần kiêng uống nước lạnh và xịt rửa mũi nước muối sinh lý.',
            'description' => "Nội soi vòm họng niêm mạc xung huyết, có dịch mủ rải rác khe giữa.",
            'prescription' => [
                ['name' => 'Augmentin 1g', 'dosage' => '2 viên/ngày chia 2 lần', 'duration' => '5 ngày'],
                ['name' => 'Otrivin 0.1%', 'dosage' => 'Xịt mũi 2 lần/ngày', 'duration' => '3 ngày'],
            ],
        ]);

        AppointmentStatusHistory::create([
            'appointment_id' => $app2->id,
            'from_status' => Appointment::STATUS_CONFIRMED,
            'to_status' => Appointment::STATUS_COMPLETED,
            'changed_by_user_id' => $doctors[1]->user_id,
            'action_role' => 'doctor',
            'note' => 'Bác sĩ hoàn thành khám bệnh và kê đơn thuốc',
        ]);

        // Appointment 3: Pending (Tomorrow)
        $slot3 = TimeSlot::where('date', $tomorrow)->where('capacity', 1)->first();
        if ($slot3) {
            $app3 = Appointment::create([
                'appointment_code' => Appointment::generateCode(),
                'patient_id' => $patients[2]->id,
                'doctor_id' => $slot3->doctor_id,
                'service_id' => $services[3]->id,
                'time_slot_id' => $slot3->id,
                'appointment_date' => $tomorrow . ' ' . $slot3->start_time,
                'medical_examination' => 'Ca sáng (07:30 - 11:30)',
                'status' => Appointment::STATUS_PENDING,
                'payment_status' => Appointment::PAYMENT_FAILED,
                'note' => 'Đưa bé đi kiểm tra dinh dưỡng và khám đường hô hấp định kỳ.',
            ]);
            $slot3->increment('booked_count');

            AppointmentStatusHistory::create([
                'appointment_id' => $app3->id,
                'from_status' => null,
                'to_status' => Appointment::STATUS_PENDING,
                'changed_by_user_id' => $patients[2]->user_id,
                'action_role' => 'patient',
                'note' => 'Bệnh nhân tạo lịch hẹn mới',
            ]);
        }

        // Appointment 4: Cancelled (F07)
        $app4 = Appointment::create([
            'appointment_code' => Appointment::generateCode(),
            'patient_id' => $patients[3]->id,
            'doctor_id' => $doctors[3]->id,
            'service_id' => $services[4]->id,
            'appointment_date' => $tomorrow . ' 14:00:00',
            'medical_examination' => 'Ca chiều (13:00 - 17:00)',
            'status' => Appointment::STATUS_CANCELLED,
            'payment_status' => Appointment::PAYMENT_FAILED,
            'cancellation_reason' => 'Bận lịch công tác đột xuất vào ngày mai, xin dời sang tuần sau.',
            'cancelled_at' => now(),
            'cancelled_by' => $patients[3]->user_id,
            'refund_status' => 'none',
            'note' => 'Đã hủy lịch hẹn.',
        ]);

        // 9. Medicines Seeding
        if (\App\Models\Medicine::count() === 0) {
            $meds = [
                ['name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'brand_name' => 'Panadol', 'category' => 'Giảm đau, hạ sốt', 'dosage_form' => 'Viên nén', 'strength' => '500mg', 'unit' => 'Viên', 'price' => 2000, 'stock' => 500, 'usage' => 'Uống 1-2 viên khi sốt trên 38.5 độ C'],
                ['name' => 'Amoxicillin 500mg', 'generic_name' => 'Amoxicillin', 'brand_name' => 'Clamoxyl', 'category' => 'Kháng sinh', 'dosage_form' => 'Viên nang', 'strength' => '500mg', 'unit' => 'Viên', 'price' => 5000, 'stock' => 300, 'usage' => 'Uống 1 viên x 3 lần/ngày sau ăn'],
                ['name' => 'Berberin 50mg', 'generic_name' => 'Berberin clorid', 'brand_name' => 'Berberin', 'category' => 'Tiêu hóa', 'dosage_form' => 'Viên nén', 'strength' => '50mg', 'unit' => 'Viên', 'price' => 1500, 'stock' => 450, 'usage' => 'Uống 4-6 viên/lần, ngày 2 lần khi tiêu chảy'],
                ['name' => 'Omeprazol 20mg', 'generic_name' => 'Omeprazole', 'brand_name' => 'Losec', 'category' => 'Tiêu hóa, dạ dày', 'dosage_form' => 'Viên nang tan trong ruột', 'strength' => '20mg', 'unit' => 'Viên', 'price' => 8000, 'stock' => 200, 'usage' => 'Uống 1 viên trước bữa ăn sáng 30 phút'],
                ['name' => 'Panadol Extra Đỏ', 'generic_name' => 'Paracetamol + Caffeine', 'brand_name' => 'Panadol Extra', 'category' => 'Giảm đau nhanh', 'dosage_form' => 'Viên nén', 'strength' => '500mg/65mg', 'unit' => 'Viên', 'price' => 3000, 'stock' => 350, 'usage' => 'Uống 1 viên khi đau đầu dữ dội'],
            ];
            foreach ($meds as $m) {
                $m['slug'] = \Illuminate\Support\Str::slug($m['name']) . '-' . rand(100, 999);
                \App\Models\Medicine::create($m);
            }
        }

        // 10. Diseases & Symptoms Seeding
        $deptList = array_values($departments);
        if (\App\Models\Disease::count() === 0 && isset($deptList[0])) {
            $diseases = [
                [
                    'name' => 'Cảm cúm thông thường (Flu)',
                    'description' => 'Nhiễm virus đường hô hấp trên gây sốt cao, ho khan, sổ mũi, đau đầu',
                    'department_id' => $deptList[1]->id ?? $deptList[0]->id,
                    'symptoms' => ['Sốt cao', 'Ho khan', 'Đau đầu', 'Sổ mũi'],
                ],
                [
                    'name' => 'Viêm họng cấp (Pharyngitis)',
                    'description' => 'Viêm niêm mạc họng do vi khuẩn hoặc virus gây đau rát họng, sốt cao',
                    'department_id' => $deptList[1]->id ?? $deptList[0]->id,
                    'symptoms' => ['Đau rát họng', 'Ho khan', 'Sốt cao'],
                ],
                [
                    'name' => 'Viêm loét dạ dày - tá tràng',
                    'description' => 'Đau thượng vị, đau dạ dày, ợ hơi, ợ chua, buồn nôn liên quan đến acid dạ dày',
                    'department_id' => $deptList[3]->id ?? $deptList[0]->id,
                    'symptoms' => ['Đau dạ dày', 'Ợ chua', 'Buồn nôn'],
                ],
                [
                    'name' => 'Tăng huyết áp & Thiếu máu cơ tim cục bộ',
                    'description' => 'Huyết áp tăng cao, đau tức ngực, khó thở khi gắng sức, tim đập nhanh, chóng mặt',
                    'department_id' => $deptList[0]->id,
                    'symptoms' => ['Đau tức ngực', 'Khó thở', 'Tim đập nhanh', 'Chóng mặt', 'Đau đầu'],
                ],
                [
                    'name' => 'Đái tháo đường Type 2',
                    'description' => 'Rối loạn chuyển hóa đường huyết mãn tính gây mệt mỏi, khát nước nhiều',
                    'department_id' => $deptList[3]->id ?? $deptList[0]->id,
                    'symptoms' => ['Mệt mỏi', 'Chóng mặt', 'Mất ngủ'],
                ],
            ];
            foreach ($diseases as $d) {
                $syms = $d['symptoms'] ?? [];
                unset($d['symptoms']);
                $diseaseModel = \App\Models\Disease::create($d);
                foreach ($syms as $symName) {
                    \App\Models\DiseaseSymptom::firstOrCreate([
                        'disease_id' => $diseaseModel->id,
                        'symptom_name' => $symName,
                    ]);
                }
            }
        }
        if (\App\Models\ServiceSymptom::count() === 0 && !empty($services)) {
            $svcSymptoms = [
                0 => ['Đau tức ngực', 'Khó thở', 'Tim đập nhanh', 'Chóng mặt'],
                1 => ['Đau tức ngực', 'Tim đập nhanh', 'Khó thở'],
                2 => ['Đau rát họng', 'Ho khan', 'Sốt cao'],
                3 => ['Sốt cao', 'Ho khan', 'Biếng ăn'],
                4 => ['Đau dạ dày', 'Ợ chua', 'Buồn nôn', 'Mệt mỏi'],
                5 => ['Đau đầu', 'Chóng mặt', 'Mất ngủ'],
            ];
            foreach ($svcSymptoms as $sIdx => $sNames) {
                if (isset($services[$sIdx])) {
                    foreach ($sNames as $sn) {
                        \App\Models\ServiceSymptom::firstOrCreate([
                            'service_id' => $services[$sIdx]->id,
                            'symptom_name' => $sn,
                        ]);
                    }
                }
            }
        }

        // 11. Test Types Seeding
        if (\App\Models\TestType::count() === 0) {
            $adminUser = User::whereHas('role', fn($q) => $q->where('name', 'like', '%admin%'))->first() ?? User::first();
            $types = [
                ['name' => 'Xét nghiệm Tổng phân tích tế bào máu ngoại vi', 'description' => 'Đánh giá 24 thông số tế bào máu', 'department_id' => $deptList[0]->id ?? null, 'price' => 150000, 'created_by' => $adminUser?->id],
                ['name' => 'Xét nghiệm Đường huyết đói (Glucose)', 'description' => 'Sàng lọc và theo dõi bệnh đái tháo đường', 'department_id' => $deptList[0]->id ?? null, 'price' => 80000, 'created_by' => $adminUser?->id],
                ['name' => 'Xét nghiệm Men gan (AST, ALT)', 'description' => 'Đánh giá tổn thương tế bào gan', 'department_id' => $deptList[0]->id ?? null, 'price' => 120000, 'created_by' => $adminUser?->id],
                ['name' => 'Xét nghiệm Chức năng thận (Ure, Creatinin)', 'description' => 'Đánh giá mức độ lọc cầu thận', 'department_id' => $deptList[0]->id ?? null, 'price' => 120000, 'created_by' => $adminUser?->id],
                ['name' => 'Nội soi Tai Mũi Họng ống mềm', 'description' => 'Khảo sát niêm mạc vòm họng và thanh quản', 'department_id' => $deptList[1]->id ?? null, 'price' => 250000, 'created_by' => $adminUser?->id],
            ];
            foreach ($types as $t) {
                \App\Models\TestType::create($t);
            }
        }

        // 12. Lab Tests Seeding
        $medRecord = MedicalRecord::first();
        if (\App\Models\LabTest::count() === 0 && $medRecord && isset($deptList[0])) {
            $adminUser = User::whereHas('role', fn($q) => $q->where('name', 'like', '%admin%'))->first() ?? User::first();
            $docUser = User::whereHas('role', fn($q) => $q->where('name', 'like', '%doctor%'))->first() ?? $adminUser;
            $firstTestType = \App\Models\TestType::where('name', 'like', '%Tổng phân tích tế bào máu%')->first();
            $secondTestType = \App\Models\TestType::where('name', 'like', '%Đường huyết%')->first();
            \App\Models\LabTest::create([
                'medical_record_id' => $medRecord->id,
                'department_id' => $deptList[0]->id,
                'test_type_id' => $firstTestType?->id,
                'test_name' => 'Xét nghiệm Tổng phân tích tế bào máu ngoại vi',
                'note' => 'Bệnh nhân sốt cao nghi nhiễm khuẩn',
                'requested_by' => $docUser?->id,
                'uploaded_by' => $adminUser?->id,
                'status' => 'completed',
            ]);
            \App\Models\LabTest::create([
                'medical_record_id' => $medRecord->id,
                'department_id' => $deptList[0]->id,
                'test_type_id' => $secondTestType?->id,
                'test_name' => 'Xét nghiệm Đường huyết đói (Glucose)',
                'note' => 'Kiểm tra đường huyết định kỳ',
                'requested_by' => $docUser?->id,
                'uploaded_by' => null,
                'status' => 'requested',
            ]);
        }
    }
}
