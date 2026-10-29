@extends('layouts.admin')

@section('title', 'Quản lý Kết quả Xét nghiệm')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-vials text-primary mr-2"></i>Quản lý Kết quả Xét nghiệm Cận lâm sàng
        </h1>
        <p class="text-muted small mb-0">Tiếp nhận phiếu chỉ định từ bác sĩ, tải lên hình ảnh kết quả và nhận xét cận lâm sàng</p>
    </div>
    <div class="btn-group btn-group-sm mt-2 mt-sm-0 shadow-sm">
        <a href="{{ route('admin.lab_tests.index', array_filter(['search' => request('search'), 'department_id' => request('department_id')])) }}"
           class="btn {{ !request('status') ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
            Tất cả
        </a>
        <a href="{{ route('admin.lab_tests.index', array_filter(['status' => 'requested', 'search' => request('search'), 'department_id' => request('department_id')])) }}"
           class="btn {{ request('status') === 'requested' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning text-dark bg-white' }}">
            <i class="fas fa-hourglass-half mr-1"></i> Chờ kết quả
        </a>
        <a href="{{ route('admin.lab_tests.index', array_filter(['status' => 'completed', 'search' => request('search'), 'department_id' => request('department_id')])) }}"
           class="btn {{ request('status') === 'completed' ? 'btn-success font-weight-bold' : 'btn-outline-success bg-white' }}">
            <i class="fas fa-check-circle mr-1"></i> Đã có kết quả
        </a>
    </div>
</div>

{{-- Flash Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Search & Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-1"></i> Tìm kiếm & Lọc chỉ định xét nghiệm
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.lab_tests.index') }}" method="GET" class="form-row align-items-end">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="col-md-6 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TÌM KIẾM</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên bệnh nhân, SĐT, tên xét nghiệm hoặc bác sĩ chỉ định...">
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">KHOA THỰC HIỆN</label>
                <select name="department_id" class="form-control">
                    <option value="">-- Tất cả chuyên khoa --</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-1">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
                @if(request()->filled('search') || request()->filled('department_id') || request()->filled('status'))
                    <a href="{{ route('admin.lab_tests.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list mr-1"></i> Danh sách các chỉ định xét nghiệm từ bác sĩ
        </h6>
        @php $testList = $labTests ?? ($tests ?? collect()); @endphp
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($testList, 'total') ? $testList->total() : $testList->count() }} phiếu
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th>Loại xét nghiệm & Khoa thực hiện</th>
                        <th>Bệnh nhân & Bác sĩ chỉ định</th>
                        <th style="width: 190px;">Thời gian & Trạng thái</th>
                        <th class="text-center" style="width: 170px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testList as $test)
                        @php
                            $isDone = ($test->status === 'completed' || !empty($test->image));
                            $docName = optional($test->doctor)->name ?? 'Bác sĩ chuyên khoa';
                            if (!\Illuminate\Support\Str::startsWith($docName, 'BS.')) {
                                $docName = 'BS. ' . $docName;
                            }
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($testList, 'firstItem') ? $testList->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-microscope mr-1 text-info"></i>{{ $test->test_name ?? optional($test->test_type)->name ?? 'Xét nghiệm cận lâm sàng' }}
                                    <span class="badge badge-light border text-muted ml-1">#LAB-{{ str_pad($test->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-hospital mr-1"></i>{{ $test->department->name ?? optional(optional($test->test_type)->department)->name ?? 'Khoa Xét nghiệm' }}
                                </small>
                                @if($test->note)
                                    <small class="text-gray-600 font-italic d-block mt-1">Ghi chú: {{ \Illuminate\Support\Str::limit($test->note, 80) }}</small>
                                @endif
                            </td>

                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    <i class="fas fa-user-injured mr-1 text-warning"></i>{{ optional(optional($test->record)->patient)->name ?? 'Bệnh nhân' }}
                                </div>
                                <div class="small text-muted mt-1">
                                    Chỉ định bởi: <strong>{{ $docName }}</strong>
                                </div>
                                @if($test->medical_record_id)
                                    <div class="small mt-1">
                                        <a href="{{ route('admin.medical-records.show', $test->medical_record_id) }}" class="text-info font-weight-bold">
                                            <i class="fas fa-file-medical-alt mr-1"></i>Xem EMR #{{ str_pad($test->medical_record_id, 4, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </div>
                                @endif
                            </td>

                            <td class="align-middle">
                                <div class="small text-gray-700">
                                    <i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($test->created_at)->format('d/m/Y H:i') }}
                                </div>
                                <div class="mt-1">
                                    @if($isDone)
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Đã có kết quả</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i>Chờ kết quả</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center align-middle">
                                <button type="button"
                                        class="btn btn-primary btn-sm px-2 mr-1 shadow-sm js-open-lab-upload"
                                        data-action="{{ route('admin.lab_tests.saveUpload', $test->id) }}"
                                        data-test="{{ $test->test_name ?? optional($test->test_type)->name ?? 'Xét nghiệm' }}"
                                        data-patient="{{ optional(optional($test->record)->patient)->name ?? 'Bệnh nhân' }}"
                                        data-note="{{ $test->note ?? '' }}"
                                        title="Tải lên kết quả nhanh">
                                    <i class="fas fa-upload mr-1"></i> {{ $isDone ? 'Sửa KQ' : 'Trả KQ' }}
                                </button>
                                @if(!empty($test->image))
                                    <button type="button"
                                            class="btn btn-info btn-circle btn-sm mr-1 js-open-lab-preview"
                                            data-img="{{ asset('storage/' . $test->image) }}"
                                            data-test="{{ $test->test_name ?? 'Xét nghiệm' }}"
                                            data-patient="{{ optional(optional($test->record)->patient)->name ?? 'Bệnh nhân' }}"
                                            title="Xem ảnh kết quả">
                                        <i class="fas fa-image"></i>
                                    </button>
                                @endif
                                <form action="{{ route('admin.lab_tests.destroy', $test->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi xét nghiệm này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa kết quả">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy chỉ định xét nghiệm nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($testList, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $testList->firstItem() ?? 0 }} - {{ $testList->lastItem() ?? 0 }} trong tổng số {{ $testList->total() }} chỉ định
                </div>
                <div>
                    {{ $testList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal Trả Kết Quả Xét Nghiệm Nhanh (Inline Upload + Image Preview) --}}
<div class="modal fade" id="quickLabUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="quickLabUploadForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-vials mr-1"></i> Trả kết quả xét nghiệm cận lâm sàng
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-2 mb-3 bg-light rounded border small">
                        <div>Bệnh nhân: <strong id="modalLabPatient" class="text-primary">-</strong></div>
                        <div>Chỉ định: <strong id="modalLabTestName">-</strong></div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small text-gray-800">1. Chọn file ảnh kết quả xét nghiệm <span class="text-danger">*</span></label>
                        <input type="file" name="image" id="modalLabImageInput" class="form-control-file" accept="image/*" required>
                        <div id="modalLabPreviewWrap" class="mt-2 text-center d-none">
                            <img id="modalLabPreviewImg" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-gray-800">2. Nhận xét / Ghi chú kết quả cận lâm sàng</label>
                        <textarea name="note" id="modalLabNoteInput" rows="2" class="form-control" placeholder="Nhập chỉ số hoặc nhận xét kết quả xét nghiệm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                        <i class="fas fa-check-circle mr-1"></i> Lưu &amp; Trả kết quả ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Xem Ảnh Kết Quả (Lightbox) --}}
<div class="modal fade" id="labImagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-weight-bold text-primary" id="lightboxTitle">Kết quả xét nghiệm</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center bg-dark p-3">
                <img id="lightboxImg" src="" alt="Kết quả" class="img-fluid rounded" style="max-height: 75vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('quickLabUploadForm');
    const imgInput = document.getElementById('modalLabImageInput');
    const previewWrap = document.getElementById('modalLabPreviewWrap');
    const previewImg = document.getElementById('modalLabPreviewImg');

    document.querySelectorAll('.js-open-lab-upload').forEach(btn => {
        btn.addEventListener('click', function () {
            uploadForm.action = this.getAttribute('data-action');
            document.getElementById('modalLabPatient').textContent = this.getAttribute('data-patient');
            document.getElementById('modalLabTestName').textContent = this.getAttribute('data-test');
            document.getElementById('modalLabNoteInput').value = this.getAttribute('data-note') || '';
            if (imgInput) imgInput.value = '';
            if (previewWrap) previewWrap.classList.add('d-none');
            if (window.jQuery) {
                window.jQuery('#quickLabUploadModal').modal('show');
            }
        });
    });

    if (imgInput && previewWrap && previewImg) {
        imgInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                previewWrap.classList.add('d-none');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewWrap.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    document.querySelectorAll('.js-open-lab-preview').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('lightboxTitle').textContent = this.getAttribute('data-test') + ' — BN: ' + this.getAttribute('data-patient');
            document.getElementById('lightboxImg').src = this.getAttribute('data-img');
            if (window.jQuery) {
                window.jQuery('#labImagePreviewModal').modal('show');
            }
        });
    });
});
</script>
@endpush
@endsection
