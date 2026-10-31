<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\TestType;
use Illuminate\Http\Request;

class TestTypeController extends Controller
{
    // Hiển thị danh sách loại xét nghiệm kèm tìm kiếm & lọc chuyên khoa
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');

        $types = TestType::with('department')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('admin.test_types.index', compact('types', 'departments'));
    }

    // Hiển thị form tạo loại xét nghiệm mới
    public function create()
    {
        $departments = Department::all();

        return view('admin.test_types.create', compact('departments'));
    }

    // Lưu loại xét nghiệm mới vào database
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $price = ($request->filled('price') && is_numeric($request->input('price')))
            ? (float) $request->input('price')
            : 150000;

        // Tạo loại xét nghiệm mới
        TestType::create([
            'name' => $request->name,
            'description' => $request->description,
            'department_id' => $request->department_id,
            'price' => $price,
            'created_by' => auth('web_admin')->id() ?? auth()->id(),
        ]);

        return redirect()->route('admin.test-types.index')->with('success', 'Đã thêm loại xét nghiệm mới!');
    }

    // Hiển thị form chỉnh sửa loại xét nghiệm
    public function edit($id)
    {
        $type = TestType::findOrFail($id);
        $departments = Department::all();

        return view('admin.test_types.edit', compact('type', 'departments'));
    }

    // Cập nhật thông tin loại xét nghiệm
    public function update(Request $request, $id)
    {
        $type = TestType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description', 'department_id');
        if ($request->filled('price') && is_numeric($request->input('price'))) {
            $data['price'] = (float) $request->input('price');
        }

        // Cập nhật thông tin loại xét nghiệm
        $type->update($data);

        return redirect()->route('admin.test-types.index')->with('success', 'Đã cập nhật loại xét nghiệm!');
    }

    // Xóa loại xét nghiệm khỏi database (chặn nếu đã phát sinh chỉ định CLS)
    public function destroy($id)
    {
        $type = TestType::findOrFail($id);

        if (\App\Models\LabTest::where('test_type_id', $type->id)->exists()) {
            return back()->with('error', 'Không thể xóa loại xét nghiệm này vì đã có phiếu xét nghiệm cận lâm sàng sử dụng.');
        }

        $type->delete();

        return back()->with('success', 'Đã xóa loại xét nghiệm.');
    }
}
