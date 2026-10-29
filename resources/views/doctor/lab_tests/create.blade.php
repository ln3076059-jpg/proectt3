@extends('layouts.doctor')

@section('title', 'Tạo yêu cầu xét nghiệm mới')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-vial text-primary mr-2"></i>Chỉ định xét nghiệm Cận lâm sàng
        </h1>
        <p class="text-muted small mb-0">Hồ sơ bệnh án: #EMR-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }} | Bệnh nhân: {{ $record->patient->name ?? 'Bệnh nhân' }}</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="{{ route('doctor.patient.record', $record->appointment_id) }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại Bệnh án
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin yêu cầu xét nghiệm</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('doctor.lab_tests.store', ['record' => $record->id]) }}" method="POST">
                    @csrf

                    @php
                        $testTypes = \App\Models\TestType::with('department')->orderBy('name')->get();
                    @endphp

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Chọn nhanh xét nghiệm thường quy:</label>
                        <div class="d-flex flex-wrap mb-2">
                            @foreach([
                                'Tổng phân tích tế bào máu ngoại vi',
                                'Sinh hóa máu (Đường huyết, Mỡ máu, Gan, Thận)',
                                'Tổng phân tích nước tiểu 10 thông số',
                                'Siêu âm ổ bụng tổng quát',
                                'X-Quang tim phổi thẳng',
                                'Điện tâm đồ (ECG)'
                            ] as $quickTest)
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mr-2 mb-2 quick-test-chip" data-test="{{ $quickTest }}">
                                    <i class="fas fa-plus-circle mr-1"></i>{{ $quickTest }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Hoặc chọn từ danh mục loại xét nghiệm chuẩn</label>
                        <select id="testTypeSelect" class="form-control">
                            <option value="" data-name="" data-dept="">-- Chọn loại xét nghiệm mẫu --</option>
                            @foreach($testTypes as $tt)
                                <option value="{{ $tt->id }}" data-name="{{ $tt->name }}" data-dept="{{ $tt->department_id }}">
                                    {{ $tt->name }} @if($tt->department) ({{ $tt->department->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Chọn loại mẫu để tự động điền Tên xét nghiệm và Khoa phòng phụ trách.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Tên xét nghiệm chỉ định <span class="text-danger">*</span></label>
                        <input type="text" name="test_name" id="testNameInput" class="form-control @error('test_name') is-invalid @enderror" value="{{ old('test_name') }}" placeholder="Ví dụ: Tổng phân tích tế bào máu ngoại vi..." required>
                        @error('test_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Khoa / Phòng xét nghiệm phụ trách <span class="text-danger">*</span></label>
                        <select name="department_id" id="departmentSelect" class="form-control @error('department_id') is-invalid @enderror" required>
                            <option value="">-- Chọn khoa phòng --</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Ghi chú & Chỉ định lâm sàng</label>
                        <textarea name="note" rows="3" class="form-control" placeholder="Yêu cầu cụ thể gửi kỹ thuật viên xét nghiệm (VD: Bệnh nhân đã nhịn ăn sáng, cần ưu tiên trả kết quả sớm)...">{{ old('note') }}</textarea>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split shadow-sm">
                        <span class="icon text-white-50"><i class="fas fa-paper-plane"></i></span>
                        <span class="text font-weight-bold">Gửi chỉ định xét nghiệm</span>
                    </button>
                    <a href="{{ route('doctor.patient.record', $record->appointment_id) }}" class="btn btn-secondary btn-icon-split shadow-sm ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="fas fa-info-circle mr-1"></i> Lưu ý quy trình
                </h6>
            </div>
            <div class="card-body small text-muted">
                <p>Sau khi bác sĩ gửi chỉ định, thông tin sẽ được chuyển tới phân hệ <strong>Cận lâm sàng</strong> của phòng khám.</p>
                <p class="mb-0">Kỹ thuật viên sẽ tiến hành lấy mẫu, phân tích và tải file kết quả (kèm hình ảnh). Kết quả sẽ tự động đồng bộ vào Hồ sơ bệnh án EMR của bệnh nhân.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('testTypeSelect');
        const nameInput = document.getElementById('testNameInput');
        const deptSel = document.getElementById('departmentSelect');

        if (sel && nameInput && deptSel) {
            sel.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const n = opt.getAttribute('data-name') || '';
                const d = opt.getAttribute('data-dept') || '';
                if (n) nameInput.value = n;
                if (d) deptSel.value = d;
            });
        }

        document.querySelectorAll('.quick-test-chip').forEach(btn => {
            btn.addEventListener('click', function () {
                const testName = this.getAttribute('data-test');
                if (nameInput) {
                    nameInput.value = testName;
                    nameInput.focus();
                }
                if (deptSel && !deptSel.value && deptSel.options.length > 1) {
                    deptSel.selectedIndex = 1;
                }
            });
        });
    });
</script>
@endpush
