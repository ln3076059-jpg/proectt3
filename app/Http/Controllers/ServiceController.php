<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Service;
use App\Models\ServiceSymptom;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Hiển thị danh sách dịch vụ kèm tìm kiếm & lọc theo chuyên khoa
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');

        $services = Service::with(['department', 'symptoms'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('symptoms', fn($sq) => $sq->where('symptom_name', 'like', "%{$search}%"));
                });
            })
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('admin.services.index', compact('services', 'departments'));
    }

    // Hiển thị form tạo dịch vụ mới
    public function create()
    {
        $departments = Department::all();

        return view('admin.services.create', compact('departments'));
    }

    // Lưu dịch vụ mới vào database
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'department_id' => 'required|exists:departments,id',
            'image' => 'nullable|image|max:2048',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'nullable|string',
        ]);

        // Lấy dữ liệu hợp lệ
        $data = $request->only('name', 'description', 'price', 'department_id');

        // Xử lý upload ảnh nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        // Tạo dịch vụ mới
        $service = Service::create($data);

        // Lưu triệu chứng liên quan
        if ($request->has('symptoms')) {
            $symptoms = array_filter($request->input('symptoms', []), function ($symptom) {
                return !empty(trim($symptom));
            });

            foreach ($symptoms as $symptomName) {
                ServiceSymptom::create([
                    'service_id' => $service->id,
                    'symptom_name' => trim($symptomName),
                ]);
            }
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Thêm dịch vụ thành công!');
    }

    // Hiển thị form chỉnh sửa dịch vụ
    public function edit(Service $service)
    {
        $departments = Department::all();
        // Load triệu chứng hiện có
        $service->load('symptoms');

        return view('admin.services.edit', compact('service', 'departments'));
    }

    // Cập nhật thông tin dịch vụ trong database
    public function update(Request $request, Service $service)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'department_id' => 'required|exists:departments,id',
            'image' => 'nullable|image|max:2048',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'nullable|string|max:255',
        ]);

        // Lấy dữ liệu hợp lệ
        $data = $request->only('name', 'description', 'price', 'department_id');

        // Xử lý upload ảnh mới nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        // Cập nhật dịch vụ
        $service->update($data);

        // Xóa tất cả triệu chứng cũ và thêm lại
        ServiceSymptom::where('service_id', $service->id)->delete();

        // Lưu lại triệu chứng mới
        if ($request->has('symptoms')) {
            $symptoms = array_filter($request->input('symptoms', []), function ($symptom) {
                return !empty(trim($symptom));
            });

            foreach ($symptoms as $symptomName) {
                ServiceSymptom::create([
                    'service_id' => $service->id,
                    'symptom_name' => trim($symptomName),
                ]);
            }
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Cập nhật dịch vụ thành công!');
    }

    // Xóa dịch vụ khỏi database
    public function destroy(Service $service)
    {
        // Không cho phép xóa nếu đã có lịch hẹn tham chiếu tới dịch vụ này
        if (Appointment::where('service_id', $service->id)->exists()) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Không thể xóa dịch vụ vì đã có lịch hẹn sử dụng dịch vụ này. Vui lòng hủy/đổi lịch liên quan trước.');
        }

        try {
            $service->delete();

            return redirect()->route('admin.services.index')
                ->with('success', 'Xóa dịch vụ thành công!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Xóa dịch vụ thất bại. Vui lòng thử lại sau.');
        }
    }
}
