<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Hiển thị trang chính của hồ sơ cá nhân
    public function index(Request $request)
    {
        return redirect()->route('profile.edit');
    }

    // Hiển thị trang chỉnh sửa hồ sơ người dùng
    public function edit(Request $request): View
    {
        $user = $request->user();
        $patient = $user ? $user->patient : null;

        $completedVisitsCount = 0;
        $upcomingVisitsCount = 0;
        $savedRelatives = collect();

        if ($patient) {
            $completedVisitsCount = \App\Models\Appointment::where('patient_id', $patient->id)
                ->where('status', \App\Models\Appointment::STATUS_COMPLETED)
                ->count();

            $upcomingVisitsCount = \App\Models\Appointment::where('patient_id', $patient->id)
                ->whereIn('status', [
                    \App\Models\Appointment::STATUS_PENDING,
                    \App\Models\Appointment::STATUS_CONFIRMED,
                    \App\Models\Appointment::STATUS_CHECKED_IN,
                ])
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->count();

            $savedRelatives = \App\Models\Appointment::where('patient_id', $patient->id)
                ->where('booking_for', 'relative')
                ->whereNotNull('relative_name')
                ->where('relative_name', '!=', '')
                ->orderByDesc('id')
                ->get(['relative_name', 'relative_dob', 'relative_gender', 'relative_relationship'])
                ->unique(fn ($item) => mb_strtolower(trim($item->relative_name)))
                ->take(5)
                ->values();
        }

        return view('profile.edit', [
            'user' => $user,
            'completedVisitsCount' => $completedVisitsCount,
            'upcomingVisitsCount' => $upcomingVisitsCount,
            'savedRelatives' => $savedRelatives,
        ]);
    }

    // Cập nhật thông tin hồ sơ người dùng, đồng thời đồng bộ với bảng patients
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user(); // Lấy người dùng hiện tại
        $validated = $request->validated(); // Lấy dữ liệu đã được xác thực từ request

        // Gán dữ liệu hợp lệ vào model user
        $user->fill($validated);

        // Nếu email thay đổi → reset trạng thái xác minh email
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Lưu thông tin cập nhật vào cơ sở dữ liệu
        $user->save();

        // Xử lý hình ảnh (avatar)
        $avatarPath = ($user->patient && $user->patient->avatar) ? $user->patient->avatar : null; // Lấy đường dẫn avatar cũ (nếu có)

        // Nếu người dùng có upload avatar mới
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ khỏi bộ nhớ nếu tồn tại
            if (!empty($avatarPath) && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            // Lưu ảnh mới vào thư mục "patients/avatar" (trong disk public)
            $avatarPath = $request->file('avatar')->store('patients/avatar', 'public');
        }

        // Cập nhật hoặc tạo bản ghi bệnh nhân
        $user->patient()->updateOrCreate(
            ['user_id' => $user->id], // điều kiện để tìm bản ghi
            [
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'phone' => $validated['phone'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'insurance_number' => $validated['insurance_number'] ?? null,
                'avatar' => $avatarPath, // cập nhật đường dẫn ảnh đại diện
            ]
        );

        // Chuyển hướng về trang chỉnh sửa kèm thông báo cập nhật thành công
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Đổi mật khẩu người dùng hiện tại
    public function updatePassword(Request $request)
    {
        // Xác thực dữ liệu nhập
        $request->validate([
            'current_password' => 'required', // bắt buộc nhập mật khẩu hiện tại
            'password' => ['required', 'confirmed', Password::defaults()], // mật khẩu mới theo policy mặc định và phải trùng với xác nhận
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Lấy user hiện tại

        // Kiểm tra mật khẩu hiện tại có đúng không
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng.');
        }

        // Cập nhật mật khẩu mới (sau khi mã hóa)
        $user->password = Hash::make($request->password);
        $user->save();

        // Chuyển hướng kèm thông báo
        return redirect()->route('profile.edit')->with('status', 'password-updated');
    }

    // Xóa tài khoản người dùng (có yêu cầu xác nhận mật khẩu)
    public function destroy(Request $request)
    {
        $user = $request->user(); // Lấy user hiện tại

        // Xác thực mật khẩu trước khi xóa tài khoản
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        if ($user->patient && $user->patient->appointments()->exists()) {
            return back()->with('error', 'Tài khoản đã có lịch sử khám bệnh tại phòng khám nên không thể xóa hoàn toàn để bảo lưu hồ sơ y tế.');
        }

        // Đăng xuất khỏi hệ thống
        Auth::logout();

        // Xóa user (và có thể cascade xóa dữ liệu liên quan)
        $user->delete();

        // Làm mới session để đảm bảo bảo mật
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Chuyển hướng về trang chủ sau khi xóa thành công
        return redirect('/')->with('status', 'Tài khoản đã được xóa thành công.');
    }
}
