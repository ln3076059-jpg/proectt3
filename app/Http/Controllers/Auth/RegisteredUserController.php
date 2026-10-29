<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * F01: Hiển thị trang đăng ký tài khoản bệnh nhân
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * F01: Xử lý đăng ký tài khoản bệnh nhân trong một giao dịch nhất quán
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Kiểm tra tính hợp lệ của dữ liệu đầu vào
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'regex:/^0[0-9]{9}$/'],
            'birthdate' => ['nullable', 'date'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Họ và tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng. Vui lòng chọn email khác.',
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        // 2. Thực thi tạo tài khoản và hồ sơ bệnh nhân trong DB::transaction nhất quán
        $user = DB::transaction(function () use ($request) {
            // Đảm bảo chỉ cấp vai trò 'patient' (Không cho phép tự đăng ký Admin hoặc Doctor)
            $patientRole = Role::firstOrCreate(['name' => 'patient']);

            $newUser = User::create([
                'role_id' => $patientRole->id,
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone,
                'birthdate' => $request->birthdate,
                'password' => Hash::make($request->password), // Băm mật khẩu bằng Bcrypt
            ]);

            // Tạo hồ sơ bệnh nhân đồng bộ
            Patient::create([
                'user_id' => $newUser->id,
                'name' => $newUser->name,
                'email' => $newUser->email,
                'phone' => $newUser->phone,
                'birthdate' => $newUser->birthdate,
            ]);

            return $newUser;
        });

        event(new Registered($user));

        // Đăng nhập tự động sau khi đăng ký
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản bệnh nhân thành công! Chào mừng bạn đến với Phòng khám Thái Thịnh.');
    }
}
