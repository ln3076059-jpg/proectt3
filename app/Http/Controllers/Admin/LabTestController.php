<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use Illuminate\Http\Request;

class LabTestController extends Controller
{
    // Hiển thị danh sách xét nghiệm kèm bộ lọc trạng thái, chuyên khoa & tìm kiếm
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');

        $query = LabTest::with(['department', 'record.patient', 'record.appointment', 'doctor', 'test_type']);

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($request->status === 'requested') {
                $query->where('status', '!=', 'completed');
            }
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('test_name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('record.patient', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhereHas('doctor', fn($dq) => $dq->where('name', 'like', "%{$search}%"));
            });
        }

        $labTests = $query->latest()->paginate(15)->withQueryString();
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('admin.lab_tests.index', compact('labTests', 'departments'));
    }

    // Lưu xét nghiệm mới vào database
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'department_id' => 'required|exists:departments,id',
            'test_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $adminUserId = auth('web_admin')->id() ?? auth()->id();

        // Chuẩn bị dữ liệu
        $data = $request->only(['medical_record_id', 'department_id', 'test_name', 'note']);
        $data['requested_by'] = $adminUserId;
        $data['uploaded_by'] = $adminUserId;
        $data['status'] = 'completed';

        // Upload ảnh chính nếu có
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('lab_tests', 'public');
        }

        // Upload ảnh phụ nếu có
        if ($request->hasFile('images')) {
            $paths = [];

            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('lab_tests', 'public');
            }
            $data['images'] = $paths;
        }

        // Tạo xét nghiệm mới
        LabTest::create($data);

        return redirect()->route('admin.lab_tests.index')
            ->with('success', 'Thêm xét nghiệm thành công!');
    }

    // Cập nhật thông tin xét nghiệm
    public function update(Request $request, $id)
    {
        $test = LabTest::findOrFail($id);

        // Validate dữ liệu đầu vào
        $request->validate([
            'test_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Cập nhật thông tin xét nghiệm
        $test->update([
            'test_name' => $request->test_name,
            'department_id' => $request->department_id,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.lab_tests.index')
            ->with('success', 'Cập nhật xét nghiệm thành công!');
    }

    // Hiển thị form upload kết quả xét nghiệm
    public function uploadResult($id)
    {
        $test = LabTest::with(['department', 'record.patient', 'record.appointment', 'doctor'])->findOrFail($id);

        return view('admin.lab_tests.upload_result', compact('test'));
    }

    // Lưu kết quả xét nghiệm (ảnh) đã upload
    public function saveUpload(Request $request, $id)
    {
        $test = LabTest::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'note' => 'nullable|string|max:2000',
        ]);

        if (!$request->hasFile('image') && !$request->hasFile('images') && empty($test->image) && empty($test->images)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một file ảnh kết quả xét nghiệm để tải lên.');
        }

        // Upload ảnh chính nếu có
        if ($request->hasFile('image')) {
            $test->image = $request->file('image')->store('lab_tests', 'public');
        }

        // Upload ảnh phụ nếu có
        if ($request->hasFile('images')) {
            $images = [];

            foreach ($request->file('images') as $img) {
                $images[] = $img->store('lab_tests', 'public');
            }
            $test->images = $images;
        }

        if ($request->filled('note')) {
            $test->note = $request->note;
        }

        // Cập nhật trạng thái và người upload
        $test->status = 'completed';
        $test->uploaded_by = auth('web_admin')->id() ?? auth()->id();
        $test->save();

        // Ghi vết kiểm toán vào lịch sử trạng thái ca khám khi đã trả kết quả CLS
        $appointment = optional($test->record)->appointment;
        if ($appointment) {
            \App\Models\AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => $appointment->status,
                'to_status' => $appointment->status,
                'changed_by_user_id' => $test->uploaded_by,
                'action_role' => 'admin',
                'note' => "Đã trả kết quả cận lâm sàng: {$test->test_name}" . ($test->note ? " ({$test->note})" : ''),
            ]);
        }

        return redirect()->route('admin.lab_tests.index')
            ->with('success', 'Đã upload kết quả thành công.');
    }

    // Xóa xét nghiệm khỏi database
    public function destroy($id)
    {
        LabTest::findOrFail($id)->delete();

        return back()->with('success', 'Đã xóa xét nghiệm.');
    }

    /**
     * Xem / Tải xuống kết quả xét nghiệm CLS có kiểm soát phân quyền y tế (Chỉ Admin, Bác sĩ hoặc chính Bệnh nhân sở hữu hồ sơ)
     */
    public function viewAttachment(Request $request, LabTest $labTest)
    {
        $user = auth('web_admin')->user()
            ?? auth('web_doctor')->user()
            ?? auth('web')->user();

        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem kết quả xét nghiệm.');
        }

        $labTest->loadMissing(['record.appointment.patient', 'record.patient']);
        $roleName = $user->role ? strtolower(trim($user->role->name)) : 'patient';

        if (!in_array($roleName, ['admin', 'leader', 'doctor'], true)) {
            $ownerUserId = optional(optional(optional($labTest->record)->appointment)->patient)->user_id
                ?? optional(optional($labTest->record)->patient)->user_id;

            if ((int) $ownerUserId !== (int) $user->id) {
                abort(403, 'Bạn không có quyền truy cập kết quả xét nghiệm của bệnh nhân khác.');
            }
        }

        $index = $request->query('index');
        $path = $labTest->image;
        if ($index !== null && is_array($labTest->images) && isset($labTest->images[(int) $index])) {
            $path = $labTest->images[(int) $index];
        }

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            abort(404, 'Không tìm thấy tệp kết quả xét nghiệm.');
        }

        return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path));
    }
}
