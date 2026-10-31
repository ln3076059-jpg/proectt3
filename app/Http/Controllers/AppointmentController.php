<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookAppointmentRequest;
use App\Http\Requests\CancelAppointmentRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * F06: Hiển thị danh sách lịch hẹn của bệnh nhân đang đăng nhập
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Bác sĩ truy cập route này thì chuyển sang danh sách ca khám của bác sĩ
        if ($user->hasRole('doctor')) {
            return redirect()->route('doctor.appointments.index');
        }

        // Admin truy cập route này thì chuyển sang quản trị lịch hẹn
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.appointments.index');
        }

        // Bệnh nhân
        $patient = $user->patient;
        if (!$patient) {
            // Tự động đồng bộ hồ sơ bệnh nhân nếu chưa có
            $patient = $user->patient()->create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birthdate' => $user->birthdate,
                'gender' => $user->gender,
                'address' => $user->address,
            ]);
        }

        // Query lịch hẹn thuộc về bệnh nhân (kiểm tra quyền chặt chẽ ở cấp database)
        $query = Appointment::where('patient_id', $patient->id)
            ->with(['doctor.user', 'doctor.department', 'service', 'timeSlot'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('id');

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm toàn diện trên server theo mã lịch, tên người thân, tên bác sĩ hoặc dịch vụ
        if ($request->filled('search')) {
            $kw = trim((string) $request->search);
            $query->where(function ($sub) use ($kw) {
                $sub->where('appointment_code', 'like', "%{$kw}%")
                    ->orWhere('relative_name', 'like', "%{$kw}%")
                    ->orWhereHas('doctor.user', fn ($q) => $q->where('name', 'like', "%{$kw}%"))
                    ->orWhereHas('service', fn ($q) => $q->where('name', 'like', "%{$kw}%"));
            });
        }

        // Lọc theo ngày
        if ($request->filled('from_date')) {
            $query->whereDate('appointment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('appointment_date', '<=', $request->to_date);
        }

        $appointments = $query->paginate(10)->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    /**
     * F05: Hiển thị giao diện đặt lịch hẹn khám bệnh
     */
    public function create(Request $request)
    {
        $departments = Department::where('is_active', true)->get();
        $services = Service::whereHas('department', fn($q) => $q->where('is_active', true))->get();
        $doctors = Doctor::where('is_active', true)
            ->with(['user', 'department'])
            ->get();

        $selectedDepartmentId = $request->get('department_id');
        $selectedDoctorId = $request->get('doctor_id');
        $selectedServiceId = $request->get('service_id');
        $selectedDate = $request->get('date', now()->addDay()->format('Y-m-d'));

        // Lấy danh sách hồ sơ người thân đã từng đặt lịch để chọn nhanh 1 chạm
        $savedRelatives = collect();
        if (Auth::check() && Auth::user()->patient) {
            $savedRelatives = Appointment::where('patient_id', Auth::user()->patient->id)
                ->where('booking_for', 'relative')
                ->whereNotNull('relative_name')
                ->select('relative_name', 'relative_relationship', 'relative_dob', 'relative_gender')
                ->orderByDesc('id')
                ->get()
                ->unique(fn ($item) => mb_strtolower(trim((string) $item->relative_name)))
                ->values();
        }

        return view('appointments.create', compact(
            'departments',
            'services',
            'doctors',
            'selectedDepartmentId',
            'selectedDoctorId',
            'selectedServiceId',
            'selectedDate',
            'savedRelatives'
        ));
    }

    /**
     * F05: Lưu lịch hẹn mới (sử dụng AppointmentService với Row-level lock)
     */
    public function store(BookAppointmentRequest $request)
    {
        try {
            $appointment = $this->appointmentService->bookAppointment(
                $request->validated(),
                Auth::user()
            );

            if ($request->input('payment_method_choice') === 'vnpay') {
                return redirect()->route('payment.checkout', $appointment->id);
            }

            return redirect()
                ->route('appointments.show', $appointment->id)
                ->with('success', 'Đặt lịch khám bệnh thành công! Mã lịch hẹn của bạn là ' . $appointment->appointment_code . '. Vui lòng chờ phòng khám xác nhận.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tạo lịch hẹn: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * F06: Hiển thị chi tiết một lịch hẹn (có kiểm tra quyền Policy)
     */
    public function show($id)
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctor.user',
            'doctor.department',
            'service',
            'timeSlot',
            'statusHistories.changedByUser',
            'medicalRecord.prescriptionItems.medicine',
            'medicalRecord.labTests.test_type',
        ])->findOrFail($id);

        // Kiểm tra quyền: Chỉ chủ sở hữu, bác sĩ phụ trách hoặc admin mới được xem
        $this->authorize('view', $appointment);

        // Tính số bệnh nhân đang xếp hàng chờ trước ca khám này theo thứ tự ưu tiên lâm sàng:
        // 1. Đã Check-in (ưu tiên theo thời điểm checked_in_at tăng dần)
        // 2. Đã xác nhận (ưu tiên theo khung giờ appointment_date tăng dần)
        $queueAheadCount = 0;
        if (in_array($appointment->status, [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN], true)) {
            $apptDateStr = Carbon::parse($appointment->appointment_date)->toDateString();

            if ($appointment->status === Appointment::STATUS_CHECKED_IN) {
                $checkedInAt = $appointment->checked_in_at ?? $appointment->updated_at;
                $queueAheadCount = Appointment::where('doctor_id', $appointment->doctor_id)
                    ->whereDate('appointment_date', $apptDateStr)
                    ->where('status', Appointment::STATUS_CHECKED_IN)
                    ->where('id', '!=', $appointment->id)
                    ->where(function ($q) use ($checkedInAt, $appointment) {
                        $q->where('checked_in_at', '<', $checkedInAt)
                            ->orWhere(function ($sub) use ($checkedInAt, $appointment) {
                                $sub->where('checked_in_at', '=', $checkedInAt)
                                    ->where('id', '<', $appointment->id);
                            })
                            ->orWhere(function ($sub) use ($appointment) {
                                $sub->whereNull('checked_in_at')
                                    ->where('id', '<', $appointment->id);
                            });
                    })
                    ->count();
            } else {
                // Đang ở trạng thái confirmed: đứng sau toàn bộ bệnh nhân đã check_in + các bệnh nhân confirmed có giờ hẹn sớm hơn
                $checkedInTotal = Appointment::where('doctor_id', $appointment->doctor_id)
                    ->whereDate('appointment_date', $apptDateStr)
                    ->where('status', Appointment::STATUS_CHECKED_IN)
                    ->count();

                $earlierConfirmed = Appointment::where('doctor_id', $appointment->doctor_id)
                    ->whereDate('appointment_date', $apptDateStr)
                    ->where('status', Appointment::STATUS_CONFIRMED)
                    ->where('id', '!=', $appointment->id)
                    ->where(function ($q) use ($appointment) {
                        $q->where('appointment_date', '<', $appointment->appointment_date)
                            ->orWhere(function ($sub) use ($appointment) {
                                $sub->where('appointment_date', '=', $appointment->appointment_date)
                                    ->where('id', '<', $appointment->id);
                            });
                    })
                    ->count();

                $queueAheadCount = $checkedInTotal + $earlierConfirmed;
            }
        }

        return view('appointments.show', compact('appointment', 'queueAheadCount'));
    }

    /**
     * F07: Hủy lịch hẹn (sử dụng AppointmentService, khôi phục capacity an toàn)
     */
    public function cancel(CancelAppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Kiểm tra quyền Policy
        $this->authorize('cancel', $appointment);

        try {
            $reason = $request->input('reason', 'Bệnh nhân yêu cầu hủy qua hệ thống');
            $this->appointmentService->cancelAppointment($appointment, Auth::user(), $reason);

            return redirect()->route('appointments.show', $appointment->id)
                ->with('success', 'Đã hủy lịch hẹn thành công. Số chỗ của khung giờ khám đã được hoàn trả.');
        } catch (ValidationException $e) {
            $messages = implode(' ', array_map(fn($err) => implode(' ', $err), $e->errors()));
            return redirect()->route('appointments.show', $appointment->id)
                ->with('error', $messages);
        } catch (\Exception $e) {
            return redirect()->route('appointments.show', $appointment->id)
                ->with('error', 'Lỗi khi hủy lịch hẹn: ' . $e->getMessage());
        }
    }

    /**
     * Dời lịch khám bệnh sang khung giờ mới (Reschedule)
     */
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'new_time_slot_id' => 'required|exists:time_slots,id',
            'reschedule_reason' => 'nullable|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);
        $this->authorize('reschedule', $appointment);

        try {
            $this->appointmentService->rescheduleAppointment(
                $appointment,
                (int) $request->new_time_slot_id,
                Auth::user(),
                $request->reschedule_reason
            );

            return redirect()->route('appointments.show', $appointment->id)
                ->with('success', 'Dời lịch khám bệnh thành công sang khung giờ mới!');
        } catch (ValidationException $e) {
            $messages = implode(' ', array_map(fn($err) => implode(' ', $err), $e->errors()));
            return redirect()->back()->with('error', $messages);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Không thể dời lịch khám: ' . $e->getMessage());
        }
    }

    /**
     * API: Lấy danh sách khung giờ khám còn khả dụng của bác sĩ theo ngày (F03, F04)
     */
    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
        ]);

        $doctorId = (int) $request->doctor_id;
        $date = Carbon::parse($request->date)->toDateString();

        // Tự động giải phóng các lịch hẹn VNPay giữ chỗ quá hạn 15 phút trước khi trả về danh sách slot
        $this->appointmentService->releaseExpiredUnpaidOnlineBookings();

        $slots = TimeSlot::where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->where('is_active', true)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($slot) {
                $isPast = $slot->start_date_time->isPast();
                $isFull = $slot->booked_count >= $slot->capacity;
                $isAvailable = !$isPast && !$isFull && $slot->status === TimeSlot::STATUS_AVAILABLE;

                return [
                    'id' => $slot->id,
                    'start_time' => substr($slot->start_time, 0, 5),
                    'end_time' => substr($slot->end_time, 0, 5),
                    'formatted_time' => $slot->formatted_time,
                    'capacity' => $slot->capacity,
                    'booked_count' => $slot->booked_count,
                    'remaining' => max(0, $slot->capacity - $slot->booked_count),
                    'status' => $slot->status,
                    'is_available' => $isAvailable,
                    'reason' => $isPast ? 'Đã qua giờ' : ($isFull ? 'Hết chỗ' : ($slot->status !== TimeSlot::STATUS_AVAILABLE ? 'Đang khóa' : 'Còn chỗ')),
                ];
            });

        // Tìm 4 ngày gần nhất bác sĩ có khung giờ trống (để gợi ý thông minh khi ngày đang chọn hết chỗ / bác sĩ nghỉ)
        $upcomingSlots = TimeSlot::where('doctor_id', $doctorId)
            ->whereDate('date', '>=', now()->toDateString())
            ->whereDate('date', '!=', $date)
            ->where('is_active', true)
            ->where('status', TimeSlot::STATUS_AVAILABLE)
            ->whereRaw('booked_count < capacity')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->reject(fn ($s) => $s->start_date_time->isPast());

        $nextAvailableDates = $upcomingSlots
            ->groupBy(fn ($s) => Carbon::parse($s->date)->toDateString())
            ->map(function ($daySlots, $dStr) {
                return [
                    'date' => $dStr,
                    'formatted' => Carbon::parse($dStr)->format('d/m/Y'),
                    'remaining_slots' => $daySlots->count(),
                ];
            })
            ->take(4)
            ->values();

        return response()->json([
            'success' => true,
            'date' => $date,
            'doctor_id' => $doctorId,
            'slots' => $slots,
            'next_available_dates' => $nextAvailableDates,
        ]);
    }

    /**
     * API: Lấy danh sách bác sĩ theo chuyên khoa (F03)
     */
    public function getDoctors($departmentId)
    {
        $doctors = Doctor::where('department_id', $departmentId)
            ->where('is_active', true)
            ->with('user:id,name,email,phone')
            ->get();

        return response()->json($doctors);
    }

    /**
     * Xem hồ sơ bệnh án gắn liền với lịch khám (Bảo mật: kiểm tra quyền)
     */
    public function viewRecord($id)
    {
        $appointment = Appointment::with([
            'doctor.user',
            'doctor.department',
            'service',
            'patient.user',
            'medicalRecord.prescriptionItems.medicine',
            'medicalRecord.labTests.department',
        ])->findOrFail($id);

        $this->authorize('view', $appointment);

        $record = $appointment->medicalRecord;
        if ($record) {
            $this->authorize('view', $record);
        }

        $patient = $appointment->patient;

        return view('appointments.medical_record', compact('appointment', 'record', 'patient'))
            ->with('layout', 'layouts.profile');
    }
}
