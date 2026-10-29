<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DepartmentController extends Controller
{
    // Trang welcome - hiển thị danh sách khoa, bác sĩ, dịch vụ
    public function welcome()
    {
        $departments = Department::all();
        $doctors = Doctor::with('user')->get();
        $services = Service::with('department')->get();

        return view('welcome', compact('departments', 'doctors', 'services'));
    }

    // Hiển thị danh sách tất cả khoa kèm số lượng bác sĩ, dịch vụ và bộ lọc
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $isActive = $request->input('is_active');

        $departments = Department::withCount(['doctors', 'services', 'appointments'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($isActive !== null && $isActive !== '' && Schema::hasColumn('departments', 'is_active'), function ($q) use ($isActive) {
                $q->where('is_active', (bool) $isActive);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    // Hiển thị form thêm khoa mới
    public function create()
    {
        return view('admin.departments.create');
    }

    // Lưu khoa mới vào CSDL
    public function store(Request $request)
    {
        // Chuẩn bị rules validation
        $rules = [
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ];

        // Thêm rule is_active nếu cột tồn tại
        if (Schema::hasColumn('departments', 'is_active')) {
            $rules['is_active'] = 'nullable|boolean';
        }

        $request->validate($rules);

        $data = $request->only('name', 'description');

        // Thêm is_active nếu cột tồn tại
        if (Schema::hasColumn('departments', 'is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('departments', 'public');
        }

        Department::create($data);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Thêm khoa thành công!');
    }

    // Hiển thị form chỉnh sửa khoa
    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    // Cập nhật thông tin khoa
    public function update(Request $request, Department $department)
    {
        // Chuẩn bị rules validation
        $rules = [
            'name' => 'required|string|max:255|unique:departments,name,'.$department->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ];

        // Thêm rule is_active nếu cột tồn tại
        if (Schema::hasColumn('departments', 'is_active')) {
            $rules['is_active'] = 'nullable|boolean';
        }

        // Validate dữ liệu đầu vào
        $request->validate($rules);

        // Lấy dữ liệu hợp lệ
        $data = $request->only('name', 'description');

        // Thêm is_active nếu cột tồn tại
        if (Schema::hasColumn('departments', 'is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        // Xử lý upload ảnh mới nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('departments', 'public');
        }

        // Cập nhật khoa
        $department->update($data);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Cập nhật khoa thành công!');
    }

    // Xóa khoa (không cho phép xóa nếu còn bác sĩ hoặc dịch vụ thuộc khoa này)
    public function destroy(Department $department)
    {
        // Kiểm tra nếu còn bác sĩ thuộc khoa này thì không cho xóa
        if ($department->doctors()->exists()) {
            return redirect()
                ->route('admin.departments.index')
                ->with('error', 'Không thể xóa khoa vì vẫn còn bác sĩ thuộc khoa này.');
        }

        // Kiểm tra nếu có dịch vụ thuộc khoa này thì cũng không cho xóa
        if (method_exists($department, 'services') && $department->services()->exists()) {
            return redirect()
                ->route('admin.departments.index')
                ->with('error', 'Không thể xóa khoa vì vẫn còn dịch vụ thuộc khoa này.');
        }

        // Xóa khoa
        $department->delete();

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Xóa khoa thành công!');
    }
}
