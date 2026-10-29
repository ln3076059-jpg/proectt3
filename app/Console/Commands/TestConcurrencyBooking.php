<?php

namespace App\Console\Commands;

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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TestConcurrencyBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:concurrency 
                            {--capacity=1 : Sức chứa khung giờ kiểm thử (1 hoặc 5)} 
                            {--requests=20 : Số yêu cầu gửi đồng thời (mặc định 20)}
                            {--worker : Chế độ worker xử lý 1 request trong tiến trình độc lập}
                            {--user-id= : ID người dùng (chỉ dùng cho worker)}
                            {--slot-id= : ID khung giờ TimeSlot (chỉ dùng cho worker)}
                            {--service-id= : ID dịch vụ (chỉ dùng cho worker)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm thử xử lý đồng thời (Race Condition & Overbooking) với MySQL InnoDB row-locking theo Mục IV';

    /**
     * Execute the console command.
     */
    public function handle(AppointmentService $appointmentService): int
    {
        // -------------------------------------------------------------
        // CHẾ ĐỘ WORKER: Chạy trong tiến trình con riêng biệt
        // -------------------------------------------------------------
        if ($this->option('worker')) {
            return $this->handleWorker($appointmentService);
        }

        // -------------------------------------------------------------
        // CHẾ ĐỘ MASTER: Điều phối 20 tiến trình gửi đồng thời thực tế
        // -------------------------------------------------------------
        return $this->handleMaster();
    }

    /**
     * Xử lý 1 yêu cầu đặt lịch trong tiến trình con
     */
    protected function handleWorker(AppointmentService $appointmentService): int
    {
        $userId = (int) $this->option('user-id');
        $slotId = (int) $this->option('slot-id');
        $serviceId = (int) $this->option('service-id');

        $user = User::with('patient')->find($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'error' => "Không tìm thấy user {$userId}"]);
            return 1;
        }

        $timeSlot = TimeSlot::find($slotId);
        if (!$timeSlot) {
            echo json_encode(['success' => false, 'error' => "Không tìm thấy slot {$slotId}"]);
            return 1;
        }

        $start = microtime(true);

        try {
            // Thực thi đặt lịch qua Service với Transaction + lockForUpdate()
            $appointment = $appointmentService->bookAppointment([
                'doctor_id' => $timeSlot->doctor_id,
                'service_id' => $serviceId,
                'date' => $timeSlot->date->toDateString(),
                'time_slot_id' => $timeSlot->id,
                'note' => 'Yêu cầu kiểm thử đồng thời race condition',
            ], $user);

            $duration = round((microtime(true) - $start) * 1000, 2);

            echo json_encode([
                'success' => true,
                'user_id' => $userId,
                'appointment_id' => $appointment->id,
                'appointment_code' => $appointment->appointment_code,
                'duration_ms' => $duration,
                'error' => null,
            ]);

            return 0;
        } catch (ValidationException $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            $firstError = collect($e->errors())->flatten()->first() ?? $e->getMessage();

            echo json_encode([
                'success' => false,
                'user_id' => $userId,
                'duration_ms' => $duration,
                'error' => $firstError,
            ]);

            return 0;
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);

            echo json_encode([
                'success' => false,
                'user_id' => $userId,
                'duration_ms' => $duration,
                'error' => get_class($e) . ': ' . $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Điều phối 20 tiến trình song song thực tế trên Windows
     */
    protected function handleMaster(): int
    {
        $capacity = (int) $this->option('capacity');
        $numRequests = (int) $this->option('requests');

        $this->info("==========================================================================");
        $this->info("🏥 PHÒNG KHÁM THÁI THỊNH - KIỂM THỬ XỬ LÝ ĐỒNG THỜI (MỤC IV)");
        $this->info("==========================================================================");
        $this->line("Khung giờ test có Capacity = <fg=yellow>{$capacity}</> bệnh nhân");
        $this->line("Số yêu cầu song song = <fg=yellow>{$numRequests}</> yêu cầu đồng thời từ các bệnh nhân khác nhau");
        $this->line("Cơ chế bảo vệ: <fg=cyan>MySQL InnoDB Row-Level Lock (`lockForUpdate`), Transaction & Retry</>");
        $this->newLine();

        // 1. Chuẩn bị dữ liệu kiểm thử
        if (Doctor::count() === 0) {
            (new \Database\Seeders\ThaiThinhClinicDemoSeeder())->run();
        }

        $doctor = Doctor::where('is_active', true)->first();
        $service = Service::first();
        $patientRole = Role::firstOrCreate(['name' => 'patient']);

        // Tạo 20 bệnh nhân kiểm thử riêng biệt
        $testUsers = [];
        for ($i = 1; $i <= $numRequests; $i++) {
            $email = "concurrency_test_patient_{$i}@thaithinh.vn";
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Bệnh nhân Test Đồng thời #{$i}",
                    'password' => Hash::make('test123456'),
                    'role_id' => $patientRole->id,
                    'phone' => '098' . str_pad($i, 7, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                ]
            );

            Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'birthdate' => '1990-01-01',
                    'gender' => 'Nam',
                    'address' => 'Hà Nội',
                ]
            );

            $testUsers[] = $user;
        }

        // Tạo khung giờ kiểm thử riêng biệt
        $testDate = Carbon::today()->addDays(50)->toDateString();
        $schedule = WorkingSchedule::firstOrCreate(
            ['doctor_id' => $doctor->id, 'date' => $testDate],
            [
                'day_of_week' => Carbon::parse($testDate)->dayOfWeek,
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'status' => WorkingSchedule::STATUS_ACTIVE,
            ]
        );

        $timeSlot = TimeSlot::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'date' => $testDate,
                'start_time' => '10:00:00',
            ],
            [
                'working_schedule_id' => $schedule->id,
                'end_time' => '10:30:00',
                'capacity' => $capacity,
                'booked_count' => 0,
                'status' => TimeSlot::STATUS_AVAILABLE,
                'is_active' => true,
            ]
        );

        // Xóa bất kỳ lịch hẹn cũ nào còn sót lại trên slot này
        Appointment::where('time_slot_id', $timeSlot->id)->delete();
        $timeSlot->update(['booked_count' => 0, 'status' => TimeSlot::STATUS_AVAILABLE]);

        $this->info("-> Khung giờ ID: {$timeSlot->id} | Ngày: {$testDate} 10:00 - 10:30 | Capacity: {$capacity}");
        $this->info("-> Đang khởi chạy đồng thời {$numRequests} tiến trình PHP CLI độc lập...");

        // Đường dẫn PHP binary
        $phpBinary = 'D:\\xampp1\\php\\php.exe';
        if (!file_exists($phpBinary)) {
            $phpBinary = PHP_BINARY;
        }
        $artisanPath = base_path('artisan');

        // 2. Khởi tạo song song numRequests tiến trình qua proc_open
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $processes = [];
        $pipes = [];
        $startTime = microtime(true);

        for ($i = 0; $i < $numRequests; $i++) {
            $user = $testUsers[$i];
            $cmd = "\"{$phpBinary}\" \"{$artisanPath}\" test:concurrency --worker --user-id={$user->id} --slot-id={$timeSlot->id} --service-id={$service->id}";

            $proc = proc_open($cmd, $descriptors, $pipes[$i]);
            if (is_resource($proc)) {
                stream_set_blocking($pipes[$i][1], false);
                stream_set_blocking($pipes[$i][2], false);
                $processes[$i] = $proc;
            }
        }

        // 3. Đợi tất cả tiến trình hoàn thành và đọc output
        $results = [];
        $running = true;

        while ($running) {
            $running = false;
            foreach ($processes as $i => $proc) {
                $status = proc_get_status($proc);
                if ($status['running']) {
                    $running = true;
                }
            }
            if ($running) {
                usleep(20000); // 20ms
            }
        }

        $totalDuration = round((microtime(true) - $startTime) * 1000, 2);

        // Thu thập kết quả
        $successCount = 0;
        $rejectedCount = 0;
        $errorSamples = [];

        foreach ($processes as $i => $proc) {
            $stdout = stream_get_contents($pipes[$i][1]);
            $stderr = stream_get_contents($pipes[$i][2]);
            fclose($pipes[$i][0]);
            fclose($pipes[$i][1]);
            fclose($pipes[$i][2]);
            proc_close($proc);

            $data = json_decode(trim($stdout), true);
            if ($data && isset($data['success'])) {
                if ($data['success']) {
                    $successCount++;
                } else {
                    $rejectedCount++;
                    if (count($errorSamples) < 3 && !empty($data['error'])) {
                        $errorSamples[] = $data['error'];
                    }
                }
            } else {
                $rejectedCount++;
                $errorSamples[] = "Lỗi output worker: " . ($stderr ?: $stdout);
            }
        }

        // 4. Kiểm tra lại trạng thái thực tế trong database MySQL
        $timeSlot->refresh();
        $dbActiveCount = Appointment::where('time_slot_id', $timeSlot->id)
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->count();

        $this->newLine();
        $this->info("==========================================================================");
        $this->info("📊 KẾT QUẢ KIỂM THỬ ĐỒNG THỜI (CONCURRENCY REPORT)");
        $this->info("==========================================================================");

        $this->table(
            ['Chỉ số đánh giá', 'Kết quả thực tế', 'Kỳ vọng theo Đề cương (BR03, BR04)'],
            [
                ['Tổng số yêu cầu gửi đồng thời', $numRequests, $numRequests],
                ['Số yêu cầu thành công', "<fg=green>{$successCount}</>", $capacity],
                ['Số yêu cầu bị từ chối nghiệp vụ', "<fg=yellow>{$rejectedCount}</>", ($numRequests - $capacity)],
                ['Số bản ghi hiệu lực trong Database', "<fg=green>{$dbActiveCount}</>", $capacity],
                ['Booked count lưu trên TimeSlot', "<fg=green>{$timeSlot->booked_count}</>", $capacity],
                ['Tình trạng Overselling', ($dbActiveCount <= $capacity ? '<fg=green>KHÔNG XẢY RA (AN TOÀN)</>' : '<fg=red>CÓ XẢY RA OVERSELLING (LỖI)</>'), 'Tuyệt đối không vượt quá Capacity'],
                ['Thời gian hoàn thành tất cả yêu cầu', "{$totalDuration} ms", '< 3000 ms'],
            ]
        );

        if (!empty($errorSamples)) {
            $this->comment("Mẫu thông báo từ chối nhận được từ nghiệp vụ (hết chỗ):");
            foreach ($errorSamples as $sample) {
                $this->line("  • <fg=gray>{$sample}</>");
            }
        }

        $this->newLine();

        if ($successCount === $capacity && $dbActiveCount === $capacity && $rejectedCount === ($numRequests - $capacity)) {
            $this->info("🎉 KIỂM THỬ ĐẠT (PASS): Cơ chế chống trùng lịch hoạt động CHÍNH XÁC 100%!");
            return 0;
        } else {
            $this->error("❌ KIỂM THỬ THẤT BẠI (FAIL): Số lượng thành công ({$successCount}) không khớp với Capacity ({$capacity})!");
            return 1;
        }
    }
}
