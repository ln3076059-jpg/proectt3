<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DoctorAppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    private function currentDoctorUser()
    {
        return Auth::guard('web_doctor')->user() ?? Auth::user();
    }

    /**
     * Danh sách lịch hẹn của bác sĩ (chuyển hướng về dashboard kèm bộ lọc)
     */
    public function index(Request $request)
    {
        return redirect()->route('doctor.dashboard', $request->query());
    }

    /**
     * Chi tiết lịch hẹn dành cho bác sĩ
     */
    public function show(Appointment $appointment)
    {
        $user = $this->currentDoctorUser();
        if (!$user || !$user->doctor || $user->doctor->id !== $appointment->doctor_id) {
            abort(403, 'Bạn không có quyền xem lịch hẹn này.');
        }

        return redirect()->route('doctor.patient.record', $appointment->id);
    }

    /**
     * Cập nhật trạng thái lịch hẹn bởi bác sĩ (đi qua AppointmentService để đồng bộ kho & slot)
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $user = $this->currentDoctorUser();
        if (!$user || !$user->doctor || $user->doctor->id !== $appointment->doctor_id) {
            abort(403, 'Bạn không có quyền cập nhật lịch hẹn này.');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled,no_show',
        ]);

        try {
            $this->appointmentService->updateStatus(
                $appointment,
                $request->status,
                $user,
                'Bác sĩ cập nhật trạng thái'
            );

            return back()->with('success', 'Trạng thái lịch hẹn đã được cập nhật.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể cập nhật trạng thái: ' . $e->getMessage());
        }
    }

    // Bác sĩ đánh dấu lịch hẹn đã hoàn thành (chỉ bác sĩ được phân công mới có quyền)
    public function complete(Request $request, Appointment $appointment)
    {
        $user = $this->currentDoctorUser();

        if (!$user || !$user->doctor || $user->doctor->id !== $appointment->doctor_id) {
            return back()->with('error', 'Bạn không có quyền cập nhật lịch hẹn này.');
        }

        if (!in_array($appointment->status, ['confirmed', 'checked_in'])) {
            return back()->with('error', 'Chỉ có thể hoàn thành lịch hẹn đã được duyệt hoặc đã tiếp đón.');
        }

        try {
            $this->appointmentService->updateStatus(
                $appointment,
                Appointment::STATUS_COMPLETED,
                $user,
                'Bác sĩ hoàn thành khám bệnh'
            );

            return back()->with('success', 'Đã đánh dấu lịch hẹn là hoàn thành và cập nhật kho dược.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi hoàn thành ca khám: ' . $e->getMessage());
        }
    }


    // Tiếp đón bệnh nhân có mặt tại phòng chờ
    public function checkIn(Request $request, Appointment $appointment)
    {
        $user = $this->currentDoctorUser();

        if (!$user || !$user->doctor || $user->doctor->id !== $appointment->doctor_id) {
            return back()->with('error', 'Bạn không có quyền cập nhật lịch hẹn này.');
        }

        try {
            $this->appointmentService->updateStatus(
                $appointment,
                Appointment::STATUS_CHECKED_IN,
                $user,
                'Bệnh nhân đã có mặt tại phòng chờ'
            );

            return back()->with('success', 'Đã tiếp đón bệnh nhân vào phòng chờ khám.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi tiếp đón bệnh nhân: ' . $e->getMessage());
        }
    }
}

