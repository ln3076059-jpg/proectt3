@extends('layouts.admin')

@section('title', 'Tải lên kết quả xét nghiệm')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-upload text-primary mr-2"></i>Trả kết quả xét nghiệm: {{ $test->test_name ?? 'Xét nghiệm cận lâm sàng' }}
        </h1>
        <p class="text-muted small mb-0">Mã phiếu: #LAB-{{ str_pad($test->id, 4, '0', STR_PAD_LEFT) }} | Đồng bộ trực tiếp với Bệnh án điện tử của bác sĩ</p>
    </div>
    <a href="{{ route('admin.lab_tests.index') }}" class="btn btn-sm btn-secondary shadow-sm mt-2 mt-sm-0">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Đính kèm ảnh / phiếu trả lời kết quả xét nghiệm</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.lab_tests.saveUpload', $test->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">
                            Ảnh kết quả xét nghiệm chính @if(empty($test->image)) <span class="text-danger">*</span> @endif
                        </label>
                        <input type="file" name="image" id="mainLabImage" class="form-control-file @error('image') is-invalid @enderror" {{ empty($test->image) ? 'required' : '' }} accept="image/*">
                        <small class="form-text text-muted">Định dạng hỗ trợ: JPG, PNG, WEBP (Tối đa 5MB)</small>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <div id="mainImagePreviewWrapper" class="mt-2 {{ empty($test->image) ? 'd-none' : '' }}">
                            <span class="small text-muted d-block mb-1">Ảnh kết quả chính:</span>
                            <img id="mainImagePreview" src="{{ !empty($test->image) ? asset('storage/' . $test->image) : '' }}" class="img-thumbnail" style="max-height: 180px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Ảnh kết quả bổ sung (Có thể chọn nhiều ảnh)</label>
                        <input type="file" name="images[]" id="subLabImages" class="form-control-file @error('images.*') is-invalid @enderror" multiple accept="image/*">
                        <small class="form-text text-muted">Ảnh chụp vi trường, biểu đồ hoặc trang kết quả thứ 2</small>
                        @error('images.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        @if(!empty($test->images) && is_array($test->images))
                            <div class="mt-2">
                                <span class="small text-muted d-block mb-1">Các ảnh bổ sung hiện tại ({{ count($test->images) }}):</span>
                                <div class="d-flex flex-wrap">
                                    @foreach($test->images as $subImg)
                                        <a href="{{ asset('storage/' . $subImg) }}" target="_blank" class="mr-2 mb-2">
                                            <img src="{{ asset('storage/' . $subImg) }}" class="img-thumbnail" style="height: 100px; width: 100px; object-fit: cover;">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Ghi chú & Nhận xét của Kỹ thuật viên xét nghiệm</label>
                        <textarea name="note" id="labNoteInput" rows="4" class="form-control @error('note') is-invalid @enderror" placeholder="Ghi chú các chỉ số bất thường, kết luận sơ bộ...">{{ old('note', $test->note) }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                            <small class="text-muted mr-1"><i class="fas fa-bolt text-warning mr-1"></i>Mẫu nhận xét nhanh:</small>
                            @foreach([
                                'Các chỉ số xét nghiệm nằm trong giới hạn sinh học bình thường.',
                                'Phát hiện chỉ số bất thường, đề nghị bác sĩ lâm sàng đối chiếu triệu chứng.',
                                'Mẫu bệnh phẩm đạt chuẩn, đã hoàn tất phân tích và đính kèm phiếu kết quả.'
                            ] as $tpl)
                                <button type="button" class="btn btn-xs btn-light border rounded-pill px-2 py-1 mr-1 mb-1 small quick-lab-note" data-note="{{ $tpl }}">
                                    + {{ \Illuminate\Support\Str::limit($tpl, 42) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split shadow-sm">
                        <span class="icon text-white-50"><i class="fas fa-cloud-upload-alt"></i></span>
                        <span class="text font-weight-bold">Lưu & Công bố kết quả cho Bác sĩ</span>
                    </button>
                    <a href="{{ route('admin.lab_tests.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Thông tin phiếu chỉ định -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle mr-1"></i> Thông tin Phiếu chỉ định
                </h6>
            </div>
            <div class="card-body small text-gray-700">
                <p class="mb-2"><strong>Mã chỉ định:</strong> #LAB-{{ str_pad($test->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p class="mb-2"><strong>Tên xét nghiệm:</strong> <span class="text-primary font-weight-bold">{{ $test->test_name }}</span></p>
                <p class="mb-2"><strong>Khoa thực hiện:</strong> {{ $test->department->name ?? 'Khoa Xét nghiệm' }}</p>
                <hr>
                <p class="mb-2"><strong>Bệnh nhân:</strong> {{ optional(optional($test->record)->patient)->name ?? 'Bệnh nhân' }}</p>
                <p class="mb-2"><strong>Bác sĩ chỉ định:</strong> {{ optional($test->doctor)->name ?? 'Bác sĩ' }}</p>
                <p class="mb-2"><strong>Thời gian chỉ định:</strong> {{ \Carbon\Carbon::parse($test->created_at)->format('H:i - d/m/Y') }}</p>
                <p class="mb-3">
                    <strong>Trạng thái:</strong>
                    @if($test->status === 'completed')
                        <span class="badge badge-success">Đã trả kết quả</span>
                    @else
                        <span class="badge badge-warning text-dark">Đang chờ kết quả</span>
                    @endif
                </p>
                @if($test->medical_record_id)
                    <a href="{{ route('admin.medical-records.show', $test->medical_record_id) }}" class="btn btn-sm btn-outline-primary btn-block">
                        <i class="fas fa-file-medical-alt mr-1"></i> Xem Hồ sơ Bệnh án EMR
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainInput = document.getElementById('mainLabImage');
    const previewWrapper = document.getElementById('mainImagePreviewWrapper');
    const previewImg = document.getElementById('mainImagePreview');
    const noteInput = document.getElementById('labNoteInput');

    if (mainInput && previewWrapper && previewImg) {
        mainInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewWrapper.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    document.querySelectorAll('.quick-lab-note').forEach(btn => {
        btn.addEventListener('click', function () {
            const text = this.getAttribute('data-note');
            if (!noteInput) return;
            noteInput.value = noteInput.value ? (noteInput.value.trim() + '\n' + text) : text;
            noteInput.focus();
        });
    });
});
</script>
@endpush
@endsection
