<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    // Role ID cho bác sĩ trong hệ thống
    private const DOCTOR_ROLE_ID = 2;

    // Hiển thị danh sách bác sĩ kèm tìm kiếm & lọc chuyên khoa
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');
        $isActive = $request->input('is_active');

        $doctors = Doctor::with(['user', 'department'])
            ->withCount('appointments')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('specialization', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($isActive !== null && $isActive !== '', fn($q) => $q->where('is_active', (bool) $isActive))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('admin.doctors.index', compact('doctors', 'departments'));
    }

    // Hiển thị form thêm mới bác sĩ
    public function create()
    {
        $departments = Department::all();

        return view('admin.doctors.create', compact('departments'));
    }

    // Lưu bác sĩ mới vào database (sử dụng transaction)
    public function store(Request $request)
    {
        // Chuẩn hóa email và validate dữ liệu
        $this->normalizeEmail($request);
        $this->validateDoctorData($request);

        // Sử dụng transaction để đảm bảo toàn vẹn dữ liệu
        DB::transaction(function () use ($request) {
            $user = $this->createUserForDoctor($request);
            $this->createDoctorProfile($user->id, $request);
        });

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Thêm bác sĩ thành công!');
    }

    // Hiển thị form chỉnh sửa bác sĩ
    public function edit(Doctor $doctor)
    {
        $departments = Department::all();

        return view('admin.doctors.edit', compact('doctor', 'departments'));
    }

    // Cập nhật thông tin bác sĩ (sử dụng transaction)
    public function update(Request $request, Doctor $doctor)
    {
        // Chuẩn hóa email và validate dữ liệu
        $this->normalizeEmail($request);
        $this->validateDoctorData($request, $doctor);

        // Sử dụng transaction để đảm bảo toàn vẹn dữ liệu
        DB::transaction(function () use ($request, $doctor) {
            $this->updateUserInfo($doctor, $request);
            $this->updateDoctorProfile($doctor, $request);
        });

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Cập nhật bác sĩ thành công!');
    }

    // Hiển thị chi tiết bác sĩ (tạm thời chuyển về danh sách)
    public function show(Doctor $doctor)
    {
        return redirect()->route('admin.doctors.index')
            ->with('success', 'Đã chuyển về danh sách bác sĩ.');
    }

    // Xóa bác sĩ và tài khoản liên kết (không cho phép xóa nếu còn lịch hẹn, sử dụng transaction)
    public function destroy(Doctor $doctor)
    {
        // Kiểm tra nếu bác sĩ còn lịch hẹn thì không cho xóa
        if ($this->hasAppointments($doctor)) {
            return back()->with(
                'error',
                'Không thể xóa bác sĩ vì vẫn còn lịch hẹn liên quan. Vui lòng xóa/chuyển lịch hẹn trước.'
            );
        }

        // Sử dụng transaction để đảm bảo toàn vẹn dữ liệu
        DB::transaction(function () use ($doctor) {
            $user = $doctor->user;
            $doctor->delete();
            $user->delete();
        });

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Xóa bác sĩ thành công!');
    }

    // Lấy danh sách bác sĩ theo khoa (API endpoint)
    public function getDoctorsByDepartment($departmentId)
    {
        $doctors = Doctor::with('user')
            ->where('department_id', $departmentId)
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'department_id' => $doctor->department_id,
                ];
            });

        return response()->json($doctors);
    }

    // Hiển thị lịch làm việc của bác sĩ theo ngày kèm khung giờ và danh sách bệnh nhân đặt hẹn
    public function schedule(Request $request, $doctorId)
    {
        $doctor = Doctor::with(['user', 'department'])->findOrFail($doctorId);
        $today = $request->input('date', Carbon::today()->toDateString());

        // Đếm số lịch hẹn theo ca
        $morningCount = $this->countAppointmentsByShift($doctorId, $today, 'morning');
        $afternoonCount = $this->countAppointmentsByShift($doctorId, $today, 'afternoon');

        $schedules = \App\Models\WorkingSchedule::with(['timeSlots' => fn($q) => $q->orderBy('start_time')])
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $today)
            ->orderBy('start_time')
            ->get();

        $timeSlots = $schedules->flatMap->timeSlots->sortBy('start_time')->values();

        $morningCapacity = 20;
        $afternoonCapacity = 20;

        if ($timeSlots->isNotEmpty()) {
            $mCap = $timeSlots->filter(fn($s) => (int) substr($s->start_time, 0, 2) < 12)->sum('capacity');
            $aCap = $timeSlots->filter(fn($s) => (int) substr($s->start_time, 0, 2) >= 12)->sum('capacity');
            if ($mCap > 0) {
                $morningCapacity = $mCap;
            }
            if ($aCap > 0) {
                $afternoonCapacity = $aCap;
            }
        }

        $appointments = Appointment::with(['patient', 'service', 'timeSlot'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->orderBy('appointment_date')
            ->get();

        return view('admin.doctors.schedule', compact(
            'doctor',
            'today',
            'morningCount',
            'afternoonCount',
            'morningCapacity',
            'afternoonCapacity',
            'schedules',
            'timeSlots',
            'appointments'
        ));
    }


    // Chuẩn hóa email: chuyển về chữ thường và loại bỏ khoảng trắng
    private function normalizeEmail(Request $request): void
    {
        $request->merge([
            'email' => is_string($request->email) ? strtolower(trim($request->email)) : $request->email,
        ]);
    }

    // Validate dữ liệu bác sĩ
    private function validateDoctorData(Request $request, ?Doctor $doctor = null): void
    {
        // Chuẩn bị rule cho email (unique hoặc ignore nếu đang update)
        $emailRule = ['required', 'email', Rule::unique('users', 'email')];

        if ($doctor) {
            $emailRule = ['required', 'email', Rule::unique('users', 'email')->ignore($doctor->user_id)];
        }

        // Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $doctor ? 'nullable|min:6' : 'required|min:6',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'avatar' => 'nullable|image|max:2048',
        ], [
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
        ]);
    }

    // Tạo tài khoản user cho bác sĩ
    private function createUserForDoctor(Request $request): User
    {
        $doctorRoleId = \App\Models\Role::where('name', 'doctor')->value('id') ?? self::DOCTOR_ROLE_ID;

        return User::create([
            'role_id' => $doctorRoleId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'address' => $request->address,
        ]);
    }

    // Tạo hồ sơ bác sĩ
    private function createDoctorProfile(int $userId, Request $request): Doctor
    {
        $data = [
            'user_id' => $userId,
            'department_id' => $request->department_id,
            'specialization' => $request->specialization,
            'license_number' => $request->license_number,
            'description' => $request->description,
            'is_active' => true,
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('doctors/avatar', 'public');
        }

        return Doctor::create($data);
    }

    // Cập nhật thông tin user của bác sĩ
    private function updateUserInfo(Doctor $doctor, Request $request): void
    {
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'address' => $request->address,
        ];

        // Cập nhật mật khẩu nếu có
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($doctor->user) {
            $doctor->user->update($updateData);
        }
    }

    // Cập nhật thông tin hồ sơ bác sĩ
    private function updateDoctorProfile(Doctor $doctor, Request $request): void
    {
        $data = [
            'department_id' => $request->department_id,
            'specialization' => $request->specialization,
            'license_number' => $request->license_number,
            'description' => $request->description,
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('doctors/avatar', 'public');
        }

        $doctor->update($data);
    }

    // Kiểm tra xem bác sĩ có lịch hẹn hay không
    private function hasAppointments(Doctor $doctor): bool
    {
        return Appointment::where('doctor_id', $doctor->id)->exists();
    }

    // Đếm số lượng lịch hẹn theo ca khám (morning hoặc afternoon)
    private function countAppointmentsByShift(int $doctorId, string $date, string $shift): int
    {
        $shiftMap = [
            'morning' => 'Ca sáng (07:30 - 11:30)',
            'afternoon' => 'Ca chiều (13:00 - 17:00)',
        ];

        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where(function ($q) use ($shift, $shiftMap) {
                $q->where('medical_examination', $shiftMap[$shift]);
                if ($shift === 'morning') {
                    $q->orWhereHas('timeSlot', function ($sq) {
                        $sq->where('start_time', '<', '12:00:00');
                    })->orWhereTime('appointment_date', '<', '12:00:00');
                } else {
                    $q->orWhereHas('timeSlot', function ($sq) {
                        $sq->where('start_time', '>=', '12:00:00');
                    })->orWhereTime('appointment_date', '>=', '12:00:00');
                }
            })
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->count();
    }
}
