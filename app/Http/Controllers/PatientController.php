<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    // Hiển thị danh sách bệnh nhân kèm tìm kiếm (cho Bác sĩ)
    public function index(Request $request)
    {
        $user = auth('web_doctor')->user() ?? auth()->user();
        $search = trim((string) $request->input('search', ''));

        $applySearch = function ($query) use ($search) {
            if ($search !== '') {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('insurance_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }
        };

        if (request()->routeIs('doctor.*') && $user && $user->doctor) {
            $doctorId = $user->doctor->id;
            $patients = \App\Models\Patient::with('user')
                ->withCount(['appointments' => fn($q) => $q->where('doctor_id', $doctorId)])
                ->whereHas('appointments', fn($q) => $q->where('doctor_id', $doctorId))
                ->tap($applySearch)
                ->latest()
                ->paginate(15)
                ->withQueryString();

            return view('doctor.patients.index', compact('patients'));
        }

        $patients = \App\Models\Patient::with('user')
            ->withCount('appointments')
            ->tap($applySearch)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if (request()->routeIs('doctor.*')) {
            return view('doctor.patients.index', compact('patients'));
        }
        return view('admin.patients.index', compact('patients'));
    }

    // Hiển thị form tạo bệnh nhân mới
    public function create()
    {
        return redirect()->route('admin.patients.create');
    }

    // Lưu bệnh nhân mới vào database
    public function store(Request $request)
    {
        return redirect()->route('admin.patients.index');
    }

    // Hiển thị thông tin chi tiết của bệnh nhân
    public function show(string $id)
    {
        $patient = \App\Models\Patient::with(['user', 'appointments.doctor.user', 'appointments.service'])->findOrFail($id);
        if (request()->routeIs('doctor.*')) {
            return view('doctor.patients.show', compact('patient'));
        }
        return view('admin.patients.show', compact('patient'));
    }

    // Hiển thị form chỉnh sửa thông tin bệnh nhân
    public function edit(string $id)
    {
        return redirect()->route('admin.patients.edit', $id);
    }

    // Cập nhật thông tin bệnh nhân trong database
    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.patients.show', $id);
    }

    // Xóa bệnh nhân khỏi database
    public function destroy(string $id)
    {
        return redirect()->route('admin.patients.index');
    }
}
