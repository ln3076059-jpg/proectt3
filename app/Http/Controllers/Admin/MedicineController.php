<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicineController extends Controller
{
    // Hiển thị danh sách thuốc với tìm kiếm và bộ lọc kho
    public function index(Request $request)
    {
        $query = Medicine::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'low') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            } elseif ($request->stock_status === 'available') {
                $query->where('stock', '>', 10);
            }
        }

        $medicines = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Medicine::select('category')->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category');

        $stockStats = [
            'total' => Medicine::count(),
            'available' => Medicine::where('stock', '>', 10)->count(),
            'low' => Medicine::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'out' => Medicine::where('stock', '<=', 0)->count(),
        ];

        return view('admin.medicines.index', compact('medicines', 'categories', 'stockStats'));
    }


    // Hiển thị form tạo thuốc mới
    public function create()
    {
        return view('admin.medicines.create');
    }

    // Lưu thuốc mới vào database
    public function store(Request $request)
    {
        // Validate và lấy dữ liệu hợp lệ
        $data = $this->validateData($request);

        // Tạo slug tự động nếu không có
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Tạo thuốc mới
        Medicine::create($data);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Thêm thuốc thành công.');
    }

    // Hiển thị form chỉnh sửa thuốc
    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', compact('medicine'));
    }

    // Cập nhật thông tin thuốc trong database
    public function update(Request $request, Medicine $medicine)
    {
        // Validate và lấy dữ liệu hợp lệ
        $data = $this->validateData($request, $medicine->id);

        // Tạo slug tự động nếu không có
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Cập nhật thuốc
        $medicine->update($data);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Cập nhật thuốc thành công.');
    }

    // Xóa thuốc khỏi database (bảo vệ toàn vẹn đơn thuốc đã kê)
    public function destroy(Medicine $medicine)
    {
        if (\App\Models\PrescriptionItem::where('medicine_id', $medicine->id)->exists()) {
            return back()->with('error', "Không thể xóa thuốc \"{$medicine->name}\" vì đã phát sinh trong các đơn thuốc y tế. Bạn có thể cập nhật số lượng tồn kho thành 0.");
        }

        $medicine->delete();

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Xóa thuốc thành công.');
    }


    // Validate dữ liệu thuốc
    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'dosage_form' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'indications' => ['nullable', 'string'],
            'contraindications' => ['nullable', 'string'],
            'side_effects' => ['nullable', 'string'],
            'interactions' => ['nullable', 'string'],
            'dosage' => ['nullable', 'string'],
            'usage' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'is_prescription' => ['nullable', 'boolean'],
        ] + [
            'is_prescription' => $request->has('is_prescription'),
        ]);
    }
}
