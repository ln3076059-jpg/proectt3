<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Chỉ cho phép admin truy cập các hành động trong controller này
    public function __construct()
    {
        $this->middleware('admin');
    }

    // Hiển thị danh sách người dùng, hỗ trợ tìm kiếm và lọc theo vai trò
    public function index(Request $request)
    {
        $filter = $request->query('role');
        $search = trim((string) $request->query('search', ''));

        $query = User::with(['role', 'doctor.department', 'patient']);

        $validRoles = ['patient', 'doctor', 'admin', 'leader'];

        if ($filter && in_array($filter, $validRoles, true)) {
            $query->whereHas('role', function ($q) use ($filter) {
                $q->where('name', $filter);
            });
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $counts = [
            'all' => User::count(),
            'patient' => User::whereHas('role', fn($q) => $q->where('name', 'patient'))->count(),
            'doctor' => User::whereHas('role', fn($q) => $q->where('name', 'doctor'))->count(),
            'admin' => User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count(),
            'leader' => User::whereHas('role', fn($q) => $q->where('name', 'leader'))->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'counts' => $counts,
            'currentRole' => $filter,
        ]);
    }

    // Hiển thị form chỉnh sửa thông tin người dùng
    public function edit(User $user)
    {
        $roles = Role::orderBy('id')->get();
        return view('admin.users.edit', [
            'user' => $user->load(['role', 'doctor.department', 'patient']),
            'roles' => $roles,
        ]);
    }

    // Cập nhật thông tin người dùng và đồng bộ sang bảng patients / doctors (nếu có)
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female,other'],
            'birthdate' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($user, $validated, $request) {
            $updateData = [
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'role_id' => $validated['role_id'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? $user->gender,
                'birthdate' => $validated['birthdate'] ?? $user->birthdate,
                'address' => $validated['address'] ?? null,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            if ($user->patient) {
                $user->patient->update([
                    'name' => $validated['name'],
                    'email' => strtolower(trim($validated['email'])),
                    'phone' => $validated['phone'] ?? $user->patient->phone,
                    'gender' => $validated['gender'] ?? $user->patient->gender,
                    'birthdate' => $validated['birthdate'] ?? $user->patient->birthdate,
                    'address' => $validated['address'] ?? $user->patient->address,
                ]);
            }

            // Đồng bộ hồ sơ chuyên biệt nếu vai trò mới là doctor hoặc patient
            $newRole = Role::find($validated['role_id']);
            $roleName = $newRole ? strtolower(trim($newRole->name)) : '';

            if ($roleName === 'doctor' && !$user->doctor) {
                $defaultDeptId = \App\Models\Department::value('id');
                if ($defaultDeptId) {
                    \App\Models\Doctor::create([
                        'user_id' => $user->id,
                        'department_id' => $defaultDeptId,
                        'specialization' => 'Bác sĩ chuyên khoa',
                        'is_active' => true,
                    ]);
                }
            } elseif ($roleName === 'patient' && !$user->patient) {
                \App\Models\Patient::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                    'birthdate' => $user->birthdate,
                    'address' => $user->address,
                ]);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật tài khoản thành công.');
    }

    // Xóa người dùng khỏi hệ thống (kiểm tra ràng buộc dữ liệu lịch hẹn / bệnh án)
    public function destroy(User $user)
    {
        $currentAdminId = Auth::guard('web_admin')->id() ?? Auth::id();
        if ($currentAdminId === $user->id) {
            return redirect()->back()->with('error', 'Không thể tự xóa tài khoản của chính bạn.');
        }

        if ($user->patient && ($user->patient->appointments()->exists() || \App\Models\MedicalRecord::where('patient_id', $user->patient->id)->exists())) {
            return redirect()->back()->with('error', 'Không thể xóa tài khoản bệnh nhân đã phát sinh lịch hẹn hoặc hồ sơ bệnh án.');
        }

        if ($user->doctor && $user->doctor->appointments()->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa tài khoản bác sĩ đang có lịch hẹn liên quan.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Đã xóa tài khoản thành công.');
    }
}
