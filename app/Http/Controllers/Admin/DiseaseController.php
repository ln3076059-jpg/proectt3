<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Disease;
use App\Models\DiseaseSymptom;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    // Hiển thị danh sách bệnh kèm tìm kiếm & lọc theo chuyên khoa
    public function index(Request $request)
    {
        $query = Disease::with(['department', 'symptoms']);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('symptoms', fn ($sq) => $sq->where('symptom_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $diseases = $query->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('admin.diseases.index', compact('diseases', 'departments'));
    }

    // Hiển thị form tạo bệnh mới
    public function create()
    {
        $departments = Department::all();

        return view('admin.diseases.create', compact('departments'));
    }

    // Lưu bệnh mới vào database
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào (symptoms có thể là chuỗi cách nhau bởi dấu phẩy/xuống dòng hoặc mảng)
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'symptoms' => 'nullable',
        ]);

        // Lấy dữ liệu hợp lệ
        $data = $request->only('name', 'description', 'department_id');

        // Xử lý upload ảnh nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('diseases', 'public');
        }

        // Tạo bệnh mới
        $disease = Disease::create($data);

        // Lưu triệu chứng liên quan
        $this->syncSymptoms($disease, $request->input('symptoms'));

        return redirect()->route('admin.diseases.index')->with('success', 'Thêm bệnh thành công!');
    }

    // Hiển thị form chỉnh sửa bệnh
    public function edit(Disease $disease)
    {
        $departments = Department::all();
        $disease->load('symptoms');

        return view('admin.diseases.edit', compact('disease', 'departments'));
    }

    // Cập nhật thông tin bệnh trong database
    public function update(Request $request, Disease $disease)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'symptoms' => 'nullable',
        ]);

        // Lấy dữ liệu hợp lệ
        $data = $request->only('name', 'description', 'department_id');

        // Xử lý upload ảnh mới nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('diseases', 'public');
        }

        // Cập nhật bệnh
        $disease->update($data);

        // Xóa tất cả triệu chứng cũ và thêm lại
        DiseaseSymptom::where('disease_id', $disease->id)->delete();
        $this->syncSymptoms($disease, $request->input('symptoms'));

        return redirect()->route('admin.diseases.index')->with('success', 'Cập nhật bệnh thành công!');
    }

    /**
     * Đồng bộ danh sách triệu chứng từ chuỗi (phân tách bởi dấu phẩy / xuống dòng) hoặc mảng
     */
    private function syncSymptoms(Disease $disease, $rawSymptoms): void
    {
        if (empty($rawSymptoms)) {
            return;
        }

        $items = is_array($rawSymptoms)
            ? $rawSymptoms
            : preg_split('/[\r\n,;]+/', (string) $rawSymptoms);

        $symptoms = array_unique(array_filter(array_map('trim', $items), fn($s) => $s !== ''));

        foreach ($symptoms as $symptomName) {
            DiseaseSymptom::create([
                'disease_id' => $disease->id,
                'symptom_name' => mb_substr($symptomName, 0, 255),
            ]);
        }
    }

    // Xóa bệnh khỏi database
    public function destroy(Disease $disease)
    {
        $disease->delete();

        return redirect()->route('admin.diseases.index')->with('success', 'Xóa bệnh thành công!');
    }
}
