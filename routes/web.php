<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\DiseaseController as AdminDiseaseController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\MedicalRecordController as AdminMedicalRecordController;
use App\Http\Controllers\Admin\MedicineController as AdminMedicineController;
use App\Http\Controllers\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\AppointmentController;

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\DoctorAuthController;
use App\Http\Controllers\Auth\PatientAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Controllers\Doctor\DoctorRecordController;
use App\Http\Controllers\Doctor\HistoryController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 🏠 Trang chính (Home)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'welcome'])->name('home');
Route::get('/doctors', [HomeController::class, 'doctorsPage'])->name('doctors.index');
Route::get('/departments', [HomeController::class, 'departmentsPage'])->name('departments.index');
Route::get('/services', [HomeController::class, 'servicesPage'])->name('services.index');
Route::view('/introduces', 'home.introduces.index')->name('introduces.index');

// 🔎 Trang tư vấn theo triệu chứng
Route::get('/advisor', [HomeController::class, 'advisorPage'])->name('advisor.index');

// 📅 Trang / popup đặt lịch hẹn (Chuyển tiếp đồng bộ về quy trình đặt lịch 4 bước chuẩn)
Route::get('/appointment/modal', function (\Illuminate\Http\Request $request) {
    return redirect()->route('appointments.create', $request->query());
})->name('modal.appointment');

/*
//--------------------------------------------------------------------------
| Dashboard mặc định: nếu là admin/doctor thì chuyển sang dashboard riêng,
| còn lại (patient/user thường) thì tới patient.dashboard
//--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    /** @var \App\Models\User|null $user */
    $user = \Illuminate\Support\Facades\Auth::guard('web_admin')->user()
        ?? \Illuminate\Support\Facades\Auth::guard('web_doctor')->user()
        ?? \Illuminate\Support\Facades\Auth::guard('web')->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if (method_exists($user, 'hasRole')) {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('doctor')) {
            return redirect()->route('doctor.dashboard');
        }
    }

    return redirect()->route('home');
})->name('dashboard');

/*
//--------------------------------------------------------------------------
| ADMIN AUTH
//--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

/*
//--------------------------------------------------------------------------
| 🧑‍💼 ADMIN ROUTES
//--------------------------------------------------------------------------
*/
Route::middleware(['auth:web_admin', 'role:admin,leader'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // 📊 Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ⚙️ CRUD quản lý
        Route::resource('departments', AdminDepartmentController::class);
        Route::resource('doctors', AdminDoctorController::class);
        Route::resource('appointments', AdminAppointmentController::class);
        Route::resource('services', ServiceController::class);
        Route::resource('medicines', AdminMedicineController::class);
        Route::resource('diseases', AdminDiseaseController::class);
        Route::resource('patients', AdminPatientController::class);
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update', 'destroy']);

        Route::resource('medical-records', AdminMedicalRecordController::class)->only(['index', 'show']);

        // 🧪 Loại xét nghiệm (Danh mục)
        Route::resource('test-types', \App\Http\Controllers\Admin\TestTypeController::class)->except(['show']);

        // 🔄 Cập nhật trạng thái lịch hẹn, Xác nhận thu tiền tại quầy, Hoàn tiền & Quét QR Check-in nhanh
        Route::post('/appointments/quick-checkin', [AdminAppointmentController::class, 'quickCheckIn'])
            ->middleware('throttle:60,1')
            ->name('appointments.quick_checkin');
        Route::patch('/appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])
            ->name('appointments.status');
        Route::patch('/appointments/{appointment}/payment', [AdminAppointmentController::class, 'markAsPaid'])
            ->name('appointments.payment');
        Route::patch('/appointments/{appointment}/refund', [AdminAppointmentController::class, 'markAsRefunded'])
            ->name('appointments.refund');

        // 📅 Lịch làm việc & Khung giờ khám (F04, F10)
        Route::resource('schedules', \App\Http\Controllers\Admin\WorkingScheduleController::class);
        Route::patch('schedules/{schedule}/leave', [\App\Http\Controllers\Admin\WorkingScheduleController::class, 'toggleLeaveStatus'])->name('schedules.leave');
        Route::patch('schedules/slots/{slot}/toggle', [\App\Http\Controllers\Admin\WorkingScheduleController::class, 'toggleSlotStatus'])->name('schedules.slots.toggle');
        Route::patch('schedules/slots/{slot}/capacity', [\App\Http\Controllers\Admin\WorkingScheduleController::class, 'updateSlotCapacity'])->name('schedules.slots.capacity');
        Route::get('/doctors/{doctor}/schedule', [AdminDoctorController::class, 'schedule'])->name('doctors.schedule');

        // 📈 Trang thống kê
        Route::get('/statistics', [DashboardController::class, 'statistics'])->name('statistics.index');


        /*
        |--------------------------------------------------------------------------
        | 🧪 Quản lý xét nghiệm (Lab Tests)
        |--------------------------------------------------------------------------
        */
        Route::get('/lab-tests', [\App\Http\Controllers\Admin\LabTestController::class, 'index'])
            ->name('lab_tests.index');
        Route::get('/lab-tests/{id}/upload', [\App\Http\Controllers\Admin\LabTestController::class, 'uploadResult'])
            ->name('lab_tests.upload');
        Route::post('/lab-tests/{id}/upload', [\App\Http\Controllers\Admin\LabTestController::class, 'saveUpload'])
            ->name('lab_tests.saveUpload');
        Route::delete('/lab-tests/{id}', [\App\Http\Controllers\Admin\LabTestController::class, 'destroy'])
            ->name('lab_tests.destroy');

        // 💬 Chat
        Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])
            ->name('chat.index');

        Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])
            ->name('chat.send');

        Route::get('/chat/messages/{receiverId}', [\App\Http\Controllers\ChatController::class, 'getMessages'])
            ->name('chat.messages');

        Route::get('/chat/unread-count', [\App\Http\Controllers\ChatController::class, 'adminUnreadCount'])
            ->name('chat.unread_count');

        Route::get('/chat/unread-by-user', [\App\Http\Controllers\ChatController::class, 'adminUnreadByUser'])
            ->name('chat.unread_by_user');

        // Admin chat interface
        Route::get('/chat-interface', [\App\Http\Controllers\ChatController::class, 'adminChat'])
            ->name('chat.admin');
    });

/*
//--------------------------------------------------------------------------
| 🔐 DOCTOR AUTH
//--------------------------------------------------------------------------
*/
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/login', [DoctorAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [DoctorAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [DoctorAuthController::class, 'logout'])->name('logout');
});

/*
//--------------------------------------------------------------------------
| 🩺 DOCTOR ROUTES
//--------------------------------------------------------------------------
*/
Route::middleware(['auth:web_doctor', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        // 📊 Dashboard
        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');

        // 👤 Hồ sơ cá nhân
        Route::get('/profile', [DoctorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [DoctorProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [DoctorProfileController::class, 'updatePassword'])->name('password.update');

        // 📅 Lịch hẹn (F02, F11)
        Route::get('/appointments', [\App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [\App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'show'])->name('appointments.show');
        Route::put('/appointments/{appointment}/status', [\App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::post('/appointments/{appointment}/complete', [\App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'complete'])->name('appointments.complete');
        Route::post('/appointments/{appointment}/check-in', [\App\Http\Controllers\Doctor\DoctorAppointmentController::class, 'checkIn'])->name('appointments.checkin');

        // 👨‍⚕️ Bệnh nhân & Hồ sơ bệnh án bảo mật (Section VI)
        Route::get('/patient-record/{appointment}', [DoctorRecordController::class, 'showPatientRecord'])->name('patient.record');
        Route::put('/records/{record}', [DoctorRecordController::class, 'update'])->name('records.update');

        Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
        Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');

        // 📋 Hồ sơ bệnh án
        Route::get('/patients/{patient}/records', [\App\Http\Controllers\Doctor\DoctorRecordController::class, 'index'])->name('patients.records');
        Route::post('/patients/{patient}/records', [\App\Http\Controllers\Doctor\DoctorRecordController::class, 'store'])->name('patients.records.store');

        // 🧪 Bác sĩ yêu cầu xét nghiệm
        Route::get('/records/{record}/lab-tests/create', [\App\Http\Controllers\Doctor\LabTestController::class, 'create'])
            ->name('lab_tests.create');
        Route::post('/records/{record}/lab-tests', [\App\Http\Controllers\Doctor\LabTestController::class, 'store'])
            ->name('lab_tests.store');
        Route::get('/records/{record}/lab-tests', function (\App\Models\MedicalRecord $record) {
            return redirect()->route('doctor.patient.record', $record->appointment_id)
                ->with('error', 'Vui lòng gửi yêu cầu bằng biểu mẫu.');
        });

        // 🩻 Lịch sử khám bệnh của bác sĩ
        Route::get('/patient-history', [HistoryController::class, 'history'])->name('patient.history');
    });

/*
//--------------------------------------------------------------------------
| 🔐 PATIENT AUTH
//--------------------------------------------------------------------------
*/
Route::prefix('patient')->name('patient.')->group(function () {
    Route::get('/login', [PatientAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PatientAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [PatientAuthController::class, 'logout'])->name('logout');
});

/*
//--------------------------------------------------------------------------
| 👩‍🦰 PATIENT ROUTES (guard mặc định web)
| Hiện tại không dùng dashboard riêng, bệnh nhân sau khi đăng nhập sẽ
| sử dụng trang chính (route 'home'). Nếu sau này cần, có thể thêm lại
| route patient.dashboard tại đây.
//--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        // Các route riêng cho bệnh nhân (nếu có) thêm tại đây
    });

/*
|--------------------------------------------------------------------------
| 🔐 Shared Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // 👤 Hồ sơ cá nhân
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📅 Lịch hẹn — Các route tĩnh phải đặt TRƯỚC Route::resource('appointments') để không bị nhận nhầm là {appointment}
    // API lấy khung giờ khám thời gian thực (F04, F05)
    Route::get('/appointments/time-slots', [AppointmentController::class, 'getTimeSlots'])->name('appointments.time-slots');

    // API lấy bác sĩ theo khoa
    Route::get('/appointments/doctors/{departmentId}', [AppointmentController::class, 'getDoctors']);
    Route::get('/departments/{id}/doctors', [AdminDoctorController::class, 'getDoctorsByDepartment']);

    // Trang kết quả thanh toán
    Route::view('/appointments/success', 'appointments.success')->name('appointments.success');
    Route::view('/appointments/fail', 'appointments.fail')->name('appointments.fail');

    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    Route::get('/appointments/{appointment}/cancel', function (\App\Models\Appointment $appointment) {
        return redirect()->route('appointments.show', $appointment->id)
            ->with('error', 'Hành động hủy lịch hẹn yêu cầu gửi biểu mẫu (PATCH).');
    })->name('appointments.cancel.view');
    Route::get('/appointments/{appointment}/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');

    // 📋 Hồ sơ khám bệnh & Kết quả xét nghiệm bảo mật
    Route::get('/appointments/{id}/record', [AppointmentController::class, 'viewRecord'])->name('appointments.record');
    Route::get('/lab-tests/{labTest}/attachment', [\App\Http\Controllers\Admin\LabTestController::class, 'viewAttachment'])
        ->name('lab_tests.attachment');

    // 💬 Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{receiverId}', [ChatController::class, 'getMessages'])->name('chat.get');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->middleware('throttle:60,1')->name('chat.send');

    Route::get('/chat/unread-count/{senderId}', [ChatController::class, 'userUnreadCount'])->name('chat.unread_count');
});

/*
|--------------------------------------------------------------------------
| 💰 VNPay Payment Routes
|--------------------------------------------------------------------------
*/
Route::post('/vnpay_payment', [PaymentController::class, 'vnpay_payment'])->middleware(['auth', 'throttle:30,1'])->name('vnpay_payment');
Route::get('/vnpay_return', [PaymentController::class, 'vnpay_return'])->name('vnpay.return');
Route::match(['get', 'post'], '/vnpay_ipn', [PaymentController::class, 'vnpay_ipn'])->name('vnpay.ipn');

/*
|--------------------------------------------------------------------------
| 🔓 Simple GET Logout (logs out all guards)
|--------------------------------------------------------------------------
*/
Route::get('/logout', function (\Illuminate\Http\Request $request) {
    $requestedGuard = $request->query('guard');
    $refererPath = parse_url((string) $request->headers->get('referer'), PHP_URL_PATH) ?: '';

    if (!$requestedGuard) {
        if (str_starts_with($refererPath, '/admin')) {
            $requestedGuard = 'web_admin';
        } elseif (str_starts_with($refererPath, '/doctor')) {
            $requestedGuard = 'web_doctor';
        }
    }

    $user = ($requestedGuard && \Illuminate\Support\Facades\Auth::guard($requestedGuard)->check())
        ? \Illuminate\Support\Facades\Auth::guard($requestedGuard)->user()
        : (\Illuminate\Support\Facades\Auth::guard('web_admin')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('web_doctor')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('web')->user());

    $roleName = $user && $user->role ? strtolower(trim($user->role->name)) : null;

    if ($requestedGuard && in_array($requestedGuard, ['web', 'web_admin', 'web_doctor'], true)) {
        try {
            \Illuminate\Support\Facades\Auth::guard($requestedGuard)->logout();
        } catch (\Throwable $e) {
            // ignore
        }
    } else {
        foreach (['web', 'web_admin', 'web_doctor'] as $guard) {
            try {
                \Illuminate\Support\Facades\Auth::guard($guard)->logout();
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    // Chỉ hủy toàn bộ session nếu không còn guard nào đang đăng nhập
    $anyStillLoggedIn = \Illuminate\Support\Facades\Auth::guard('web')->check()
        || \Illuminate\Support\Facades\Auth::guard('web_admin')->check()
        || \Illuminate\Support\Facades\Auth::guard('web_doctor')->check();

    if (!$anyStillLoggedIn) {
        $request->session()->invalidate();
    }
    $request->session()->regenerateToken();

    if ($requestedGuard === 'web_admin' || $roleName === 'admin') {
        return redirect()->route('admin.login');
    }

    if ($requestedGuard === 'web_doctor' || $roleName === 'doctor') {
        return redirect()->route('doctor.login');
    }

    return redirect()->route('patient.login');
});

/*
|--------------------------------------------------------------------------
| 🧠 AI Chat (Giao diện AI)
|--------------------------------------------------------------------------
*/
Route::get('/ai-chat', fn() => redirect()->route('advisor.index'))->name('ai.chat');

/*
|--------------------------------------------------------------------------
| 🔧 Quên mật khẩu
|--------------------------------------------------------------------------
*/
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.store');
/*
|--------------------------------------------------------------------------
| 🔑 Auth Routes (Laravel Breeze / Jetstream / Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
