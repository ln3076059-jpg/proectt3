<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    // Hiển thị danh sách tất cả bệnh nhân có tìm kiếm và phân trang
    public function index(Request $request)
    {
        $query = Patient::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                    ->orWhere('insurance_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $patients = $query->latest()->paginate(10)->withQueryString();

        return view('admin.patients.index', compact('patients'));
    }

    // Hiển thị form thêm mới bệnh nhân
    public function create()
    {
        return view('admin.patients.create');
    }

    // Lưu thông tin bệnh nhân mới vào cơ sở dữ liệu (tạo user và patient)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:15',
            'gender' => 'nullable|string|in:male,female,other,Nam,Nữ,Khác',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:50',
        ]);

        $normalizedGender = $this->normalizeGender($request->gender);

        DB::transaction(function () use ($request, $normalizedGender) {
            $patientRoleId = \App\Models\Role::where('name', 'patient')->value('id') ?? 1;

            $initialPassword = $request->filled('password')
                ? $request->password
                : \Illuminate\Support\Str::password(12, true, true, false);

            $user = User::create([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($initialPassword),
                'role_id' => $patientRoleId, // Bệnh nhân (role_id = 1)
                'phone' => $request->phone,
                'gender' => $normalizedGender,
                'birthdate' => $request->birthdate,
                'address' => $request->address,
            ]);

            Patient::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'gender' => $normalizedGender,
                'birthdate' => $request->birthdate,
                'phone' => $request->phone,
                'address' => $request->address,
                'insurance_number' => $request->insurance_number,
            ]);
        });

        return redirect()->route('admin.patients.index')->with('success', 'Thêm bệnh nhân thành công.');
    }

    // Hiển thị chi tiết hồ sơ bệnh nhân kèm lịch sử khám
    public function show(Patient $patient)
    {
        $patient->load(['user', 'appointments.doctor.user', 'appointments.service']);

        return view('admin.patients.show', compact('patient'));
    }

    // Hiển thị form chỉnh sửa thông tin bệnh nhân
    public function edit(Patient $patient)
    {
        $patient->load('user');
        return view('admin.patients.edit', compact('patient'));
    }

    // Cập nhật thông tin bệnh nhân (cập nhật cả patients và users)
    public function update(Request $request, Patient $patient)
    {
        $userId = $patient->user_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($userId ?: 'NULL') . ',id',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other,Nam,Nữ,Khác',
            'birthdate' => 'nullable|date',
            'insurance_number' => 'nullable|string|max:50',
        ]);

        $normalizedGender = $this->normalizeGender($request->gender);

        DB::transaction(function () use ($request, $patient, $normalizedGender) {
            $patient->update([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'gender' => $normalizedGender,
                'birthdate' => $request->birthdate,
                'phone' => $request->phone,
                'address' => $request->address,
                'insurance_number' => $request->insurance_number,
            ]);

            if ($patient->user) {
                $patient->user->update([
                    'name' => $request->name,
                    'email' => strtolower(trim($request->email)),
                    'phone' => $request->phone,
                    'gender' => $normalizedGender,
                    'birthdate' => $request->birthdate,
                    'address' => $request->address,
                ]);
            }
        });

        return redirect()->route('admin.patients.index')->with('success', 'Cập nhật bệnh nhân thành công.');
    }

    private function normalizeGender(?string $gender): ?string
    {
        if (!$gender) {
            return null;
        }
        $map = [
            'Nam' => 'male',
            'nam' => 'male',
            'male' => 'male',
            'Nữ' => 'female',
            'nữ' => 'female',
            'female' => 'female',
            'Khác' => 'other',
            'khác' => 'other',
            'other' => 'other',
        ];

        return $map[trim($gender)] ?? 'other';
    }

    // Xóa bệnh nhân khỏi hệ thống (bảo vệ toàn vẹn dữ liệu)
    public function destroy(Patient $patient)
    {
        // Kiểm tra nếu bệnh nhân đã phát sinh lịch hẹn hoặc bệnh án y tế
        $hasAppointments = $patient->appointments()->exists();
        $hasMedicalRecords = MedicalRecord::where('patient_id', $patient->id)->exists();

        if ($hasAppointments || $hasMedicalRecords) {
            return back()->with('error', 'Không thể xóa hồ sơ bệnh nhân đã phát sinh lịch hẹn hoặc bệnh án để bảo đảm tính toàn vẹn y khoa.');
        }

        DB::transaction(function () use ($patient) {
            $user = $patient->user;
            $patient->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('admin.patients.index')->with('success', 'Xóa bệnh nhân thành công.');
    }
}
