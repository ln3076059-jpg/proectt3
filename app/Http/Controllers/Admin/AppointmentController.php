<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
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
     * F11: Danh sách lịch hẹn với bộ lọc tìm kiếm và phân trang
     */
    public function index(Request $request)
    {
        // Chủ động giải phóng các lịch hẹn thanh toán trực tuyến VNPay quá 15 phút chưa hoàn tất
        $this->appointmentService->releaseExpiredUnpaidOnlineBookings();

        $query = Appointment::with([
            'patient.user',
            'doctor.user',
            'doctor.department',
            'service',
            'timeSlot',
        ]);

        $this->applyFilters($query, $request);

        $appointments = $query->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::with('user')->get();
        $services = Service::all();

        return view('admin.appointments.index', compact('appointments', 'doctors', 'services'));
    }

    /**
     * F11: Hiển thị chi tiết lịch hẹn kèm lịch sử thay đổi trạng thái (Audit trail)
     */
    public function show(Appointment $appointment)
    {
        $appointment->load([
            'patient.user',
            'doctor.user',
            'doctor.department',
            'service',
            'timeSlot',
            'statusHistories.changedByUser',
            'medicalRecord.prescriptionItems.medicine',
            'medicalRecord.labTests.test_type',
        ]);

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * F11: Xác nhận lịch hẹn (chuyển sang confirmed qua AppointmentService)
     */
    public function confirm(Appointment $appointment)
    {
        try {
            $user = Auth::guard('web_admin')->user() ?: Auth::user();
            $this->appointmentService->updateStatus(
                $appointment,
                Appointment::STATUS_CONFIRMED,
                $user,
                'Quản trị viên xác nhận lịch hẹn'
            );

            return back()->with('success', 'Đã xác nhận lịch hẹn thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xác nhận lịch hẹn: ' . $e->getMessage());
        }
    }

    /**
     * F11: Cập nhật trạng thái lịch hẹn qua AppointmentService (tự động giải phóng slot nếu hủy/từ chối)
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled,rejected,no_show',
            'note' => 'nullable|string|max:500',
        ]);

        $user = Auth::guard('web_admin')->user() ?: Auth::user();

        try {
            $this->appointmentService->updateStatus(
                $appointment,
                $request->status,
                $user,
                $request->note
            );

            return back()->with('success', 'Cập nhật trạng thái lịch hẹn thành công.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage());
        }
    }

    /**
     * Xác nhận thu tiền viện phí tại quầy tiếp đón (Hỗ trợ thu mới hoặc tất toán bổ sung CLS/Thuốc sau khi đã trả phí khám VNPay)
     */
    public function markAsPaid(Request $request, Appointment $appointment)
    {
        $appointment->loadMissing([
            'patient',
            'service',
            'medicalRecord.prescriptionItems.medicine',
            'medicalRecord.labTests.test_type',
        ]);

        $user = Auth::guard('web_admin')->user() ?: Auth::user();
        $breakdown = $appointment->calculateCostBreakdown();

        $wasAlreadyPaidInitial = $appointment->payment_status === Appointment::PAYMENT_SUCCESS;
        if ($wasAlreadyPaidInitial && (float) ($breakdown['balance_due'] ?? 0) <= 0 && !$request->boolean('include_full_clinical_costs')) {
            return back()->with('info', 'Lịch hẹn này đã được tất toán toàn bộ viện phí trước đó.');
        }

        $alreadyPaidAmount = $wasAlreadyPaidInitial ? (float) ($breakdown['paid_amount'] ?? 0) : 0.0;
        $amount = (float) ($appointment->total_amount ?? $appointment->total ?? optional($appointment->service)->price ?? 0);
        $extraBillingNote = '';

        if ($request->boolean('include_full_clinical_costs') || ($wasAlreadyPaidInitial && (float) ($breakdown['balance_due'] ?? 0) > 0)) {
            $applyBhyt = $request->has('apply_bhyt') ? $request->boolean('apply_bhyt') : (bool) $breakdown['has_bhyt'];
            $amount = $applyBhyt && $breakdown['has_bhyt']
                ? (float) $breakdown['patient_copay_amount']
                : (float) $breakdown['gross_total'];
            $extraBillingNote = $applyBhyt && $breakdown['has_bhyt']
                ? " [Đã áp dụng BHYT {$breakdown['bhyt_rate_percent']}% (-" . number_format($breakdown['bhyt_covered_amount'], 0, ',', '.') . "đ)]"
                : ' [Tổng hợp viện phí: Khám + CLS + Thuốc]';
        } elseif ($request->boolean('apply_bhyt') && $breakdown['has_bhyt']) {
            $amount = round($amount * (1 - ($breakdown['bhyt_rate_percent'] / 100)), 0);
            $extraBillingNote = " [Đồng chi trả BHYT {$breakdown['bhyt_rate_percent']}%]";
        }

        $method = $request->input('payment_method', 'Thanh toán tại quầy tiếp đón');

        $updateData = [
            'payment_status' => Appointment::PAYMENT_SUCCESS,
            'payment_method' => $wasAlreadyPaidInitial && str_contains((string) $appointment->payment_method, 'VNPay')
                ? $appointment->payment_method . ' + Tất toán tại quầy'
                : $method,
            'total_amount' => $amount,
            'total' => $amount,
            'paid_at' => now(),
        ];

        $appointment->update($updateData);

        $deltaCollected = max(0.0, $amount - $alreadyPaidAmount);
        $auditText = $wasAlreadyPaidInitial && $deltaCollected > 0
            ? "Tất toán bổ sung viện phí CLS/Đơn thuốc (+ " . number_format($deltaCollected, 0, ',', '.') . " VNĐ -> Tổng thu: " . number_format($amount, 0, ',', '.') . " VNĐ - {$method}){$extraBillingNote}"
            : "Xác nhận đã thu viện phí (" . number_format($amount, 0, ',', '.') . " VNĐ - {$method}){$extraBillingNote}";

        \App\Models\AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'from_status' => $appointment->status,
            'to_status' => $appointment->status,
            'changed_by_user_id' => $user ? $user->id : null,
            'action_role' => 'admin',
            'note' => $auditText,
        ]);

        return back()->with('success', 'Đã xác nhận thu viện phí thành công.');
    }

    /**
     * Xác nhận đã hoàn tiền viện phí cho bệnh nhân (khi lịch hẹn đã thanh toán bị hủy)
     */
    public function markAsRefunded(Request $request, Appointment $appointment)
    {
        $request->validate([
            'refund_note' => 'nullable|string|max:500',
        ]);

        $user = Auth::guard('web_admin')->user() ?: Auth::user();

        try {
            $this->appointmentService->confirmRefund(
                $appointment,
                $user,
                $request->input('refund_note')
            );

            return back()->with('success', 'Đã xác nhận hoàn tiền viện phí cho bệnh nhân thành công.');
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xác nhận hoàn tiền: ' . $e->getMessage());
        }
    }

    /**
     * Quét mã QR hoặc nhập nhanh mã phiếu khám (TT-...) để Check-in 1 chạm tại Quầy Lễ tân
     */
    public function quickCheckIn(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string|max:500',
        ], [
            'qr_code.required' => 'Vui lòng quét mã QR hoặc nhập mã phiếu khám.',
        ]);

        $rawInput = trim((string) $request->input('qr_code'));
        $extractedCode = $rawInput;

        // Nếu quét chuỗi chuẩn Kiosk: PK125THAITHINH|MA:TT-20261024-0012|NGAY:...
        if (preg_match('/MA:([^|\s]+)/i', $rawInput, $matches)) {
            $extractedCode = trim($matches[1]);
        }

        $extractedCode = ltrim($extractedCode, '#');

        $appointment = Appointment::with(['patient.user', 'doctor.user', 'doctor.department', 'service', 'timeSlot'])
            ->where('appointment_code', $extractedCode)
            ->when(preg_match('/^APT-(\d+)$/i', $extractedCode, $idMatch), function ($q) use ($idMatch) {
                return $q->orWhere('id', (int) $idMatch[1]);
            })
            ->when(ctype_digit($extractedCode), function ($q) use ($extractedCode) {
                return $q->orWhere('id', (int) $extractedCode);
            })
            ->first();

        if (!$appointment) {
            return back()->with('error', "Không tìm thấy hồ sơ lịch hẹn nào khớp với mã: \"{$extractedCode}\".")->withInput();
        }

        if ($appointment->status === Appointment::STATUS_CHECKED_IN) {
            return redirect()->route('admin.appointments.show', $appointment->id)
                ->with('success', "Bệnh nhân {$appointment->patient_display_name} (Mã #{$appointment->appointment_code}) đã được tiếp đón (Check-in) trước đó.");
        }

        if (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])) {
            return redirect()->route('admin.appointments.show', $appointment->id)
                ->with('error', "Lịch hẹn #{$appointment->appointment_code} đang ở trạng thái \"{$appointment->status_label}\", không thể thực hiện Check-in.");
        }

        try {
            $user = Auth::guard('web_admin')->user() ?: Auth::user();
            $this->appointmentService->updateStatus(
                $appointment,
                Appointment::STATUS_CHECKED_IN,
                $user,
                "Quét mã QR tiếp đón nhanh tại Quầy Lễ tân ({$extractedCode})"
            );

            $docName = optional($appointment->doctor)->formatted_name ?? 'Bác sĩ chuyên khoa';
            $deptName = $appointment->doctor->department->name ?? 'Đa khoa';

            return redirect()->route('admin.appointments.show', $appointment->id)
                ->with('success', "Tiếp đón QR thành công! Bệnh nhân {$appointment->patient_display_name} (#{$appointment->appointment_code}) đã Check-in vào khoa {$deptName} — {$docName}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi tiếp đón QR: ' . $e->getMessage());
        }
    }

    /**
     * BR07, BR08: Không xóa vật lý lịch hẹn đã có dữ liệu liên quan
     */
    public function destroy($id)
    {
        $appointment = Appointment::with('medicalRecord')->findOrFail($id);

        if ($appointment->medicalRecord || in_array($appointment->status, [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_COMPLETED], true)) {
            return back()->with('error', 'Không thể xóa lịch hẹn đã tiếp đón hoặc đã phát sinh hồ sơ bệnh án để bảo đảm tính toàn vẹn dữ liệu.');
        }

        $user = Auth::guard('web_admin')->user() ?: Auth::user();

        // Chuyển sang trạng thái hủy thay vì xóa vĩnh viễn nếu đã xác nhận hoặc đã thanh toán
        if ($appointment->status === Appointment::STATUS_CONFIRMED || $appointment->payment_status === Appointment::PAYMENT_SUCCESS) {
            $this->appointmentService->cancelAppointment($appointment, $user, 'Quản trị viên xóa lịch');
            return back()->with('success', 'Lịch hẹn đã được chuyển sang trạng thái Đã hủy để lưu vết.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($appointment) {
            if ($appointment->time_slot_id && $appointment->status === Appointment::STATUS_PENDING) {
                $slot = \App\Models\TimeSlot::where('id', $appointment->time_slot_id)->lockForUpdate()->first();
                if ($slot) {
                    $slot->booked_count = max(0, $slot->booked_count - 1);
                    if ($slot->status === \App\Models\TimeSlot::STATUS_LOCKED && $slot->booked_count < $slot->capacity && !$slot->start_date_time->isPast()) {
                        $slot->status = \App\Models\TimeSlot::STATUS_AVAILABLE;
                    }
                    $slot->save();
                }
            }
            $appointment->delete();
        });

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Xóa lịch hẹn thành công.');
    }

    /**
     * Áp dụng các bộ lọc tìm kiếm
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                    ->orWhereHas('patient.user', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhereHas('doctor.user', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
    }
}