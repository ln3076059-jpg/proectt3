<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DoctorProfileController extends Controller
{
    // Hiển thị form chỉnh sửa hồ sơ bác sĩ
    public function edit()
    {
        $user = Auth::guard('web_doctor')->user() ?? Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();
        $departments = Department::all();

        return view('doctor.profile', compact('user', 'doctor', 'departments'));
    }

    // Cập nhật thông tin hồ sơ bác sĩ (cập nhật users và doctors, xử lý upload avatar và giấy phép)
    public function update(Request $request)
    {
        $user = Auth::guard('web_doctor')->user() ?? Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        // Validate dữ liệu đầu vào (Không cho phép bác sĩ tự đổi Khoa, Chuyên môn, Số CCHN)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'license_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Cập nhật User
        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu có
            if (!empty($doctor->avatar) && Storage::disk('public')->exists($doctor->avatar)) {
                Storage::disk('public')->delete($doctor->avatar);
            }

            // Lưu ảnh mới
            $avatarPath = $request->file('avatar')->store('doctors/avatar', 'public');
            $doctor->avatar = $avatarPath;
        }

        // Xử lý upload ảnh giấy phép hành nghề
        if ($request->hasFile('license_image')) {
            // Xóa ảnh cũ nếu có
            if (!empty($doctor->license_image) && Storage::disk('public')->exists($doctor->license_image)) {
                Storage::disk('public')->delete($doctor->license_image);
            }

            // Lưu ảnh mới
            $path = $request->file('license_image')->store('licenses', 'public');
            $doctor->license_image = $path;
        }

        // Cập nhật Doctor (chỉ cập nhật thông tin cá nhân được phép)
        $doctor->update([
            'description' => $validated['description'] ?? $doctor->description,
            'birth_date' => $validated['birth_date'] ?? $doctor->birth_date,
            'avatar' => $doctor->avatar,
            'license_image' => $doctor->license_image,
        ]);

        return redirect()->back()->with('success', 'Cập nhật hồ sơ thành công!');

    }

    // Cập nhật mật khẩu bác sĩ (yêu cầu mật khẩu hiện tại và mật khẩu mới tối thiểu 6 ký tự)
    public function updatePassword(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::guard('web_doctor')->user() ?? Auth::user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác!']);
        }

        // Cập nhật mật khẩu mới
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Cập nhật mật khẩu thành công!');
    }
}
