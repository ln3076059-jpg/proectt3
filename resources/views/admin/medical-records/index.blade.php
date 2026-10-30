@extends('layouts.admin')

@section('title', 'Quản lý Hồ sơ Bệnh án điện tử')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-file-medical-alt text-primary mr-2"></i>Quản lý Hồ sơ Bệnh án điện tử (EMR)
        </h1>
        <p class="text-muted small mb-0">Tra cứu toàn diện lịch sử chẩn đoán lâm sàng, đơn thuốc và kết quả xét nghiệm</p>
    </div>
    <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 8px;">
        <a href="{{ route('admin.medical-records.index', ['appointment_date' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-info shadow-sm">
            <i class="fas fa-calendar-day fa-sm mr-1"></i> Ca khám hôm nay
        </a>
        <button type="button" id="exportAdminEmrCsvBtn" class="btn btn-sm btn-outline-success shadow-sm">
            <i class="fas fa-file-csv fa-sm mr-1"></i> Xuất CSV bệnh án
        </button>
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

<!-- Search Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-search mr-1"></i> Tra cứu hồ sơ bệnh án
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.medical-records.index') }}" class="form-row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">BỆNH NHÂN / SĐT / CHẨN ĐOÁN</label>
                <input type="text" name="patient_name" value="{{ request('patient_name') }}" class="form-control" placeholder="Nhập tên bệnh nhân, SĐT hoặc chẩn đoán...">
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">TÊN BÁC SĨ PHỤ TRÁCH</label>
                <input type="text" name="doctor_name" value="{{ request('doctor_name') }}" class="form-control" placeholder="Nhập tên bác sĩ...">
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">NGÀY KHÁM</label>
                <input type="date" name="appointment_date" value="{{ request('appointment_date') }}" class="form-control">
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-1">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
                @if(request()->filled('patient_name') || request()->filled('doctor_name') || request()->filled('appointment_date'))
                    <a href="{{ route('admin.medical-records.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- DataTales (5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list mr-1"></i> Danh sách hồ sơ bệnh án bệnh nhân tại Thái Thịnh
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($records, 'total') ? $records->total() : $records->count() }} bệnh án
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th style="width: 175px;">Mã bệnh án & Ngày</th>
                        <th>Bệnh nhân điều trị</th>
                        <th>Bác sĩ & Chẩn đoán lâm sàng</th>
                        <th class="text-center" style="width: 135px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $record)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($records, 'firstItem') && $records->firstItem() ? $records->firstItem() + $i : $i + 1 }}
                            </td>

                            <!-- Cột 2: Mã BA & Ngày -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    #EMR-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}
                                </div>
                                <small class="text-gray-600">
                                    <i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($record->created_at ?? optional($record->appointment)->appointment_date)->format('d/m/Y H:i') }}
                                </small>
                            </td>

                            <!-- Cột 3: Bệnh nhân -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    <i class="fas fa-user-injured mr-1 text-warning"></i>{{ $record->patient->name ?? (optional(optional($record->appointment)->patient)->name ?? 'N/A') }}
                                </div>
                                <small class="text-muted"><i class="fas fa-phone mr-1 text-success"></i>{{ $record->patient->phone ?? (optional(optional($record->appointment)->patient)->phone ?? 'N/A') }}</small>
                            </td>

                            <!-- Cột 4: Bác sĩ & Chẩn đoán -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    {{ optional(optional($record->appointment)->doctor)->formatted_name ?? optional(optional(optional($record->appointment)->doctor)->user)->name ?? 'N/A' }}
                                </div>
                                <div class="small text-danger font-weight-bold mt-1">
                                    Chẩn đoán: {{ \Illuminate\Support\Str::limit($record->diagnosis ?: 'Đang theo dõi lâm sàng', 85) }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge badge-light border text-success mr-1">
                                        <i class="fas fa-pills mr-1"></i>{{ $record->prescription_items_count ?? 0 }} thuốc
                                    </span>
                                    <span class="badge badge-light border text-info">
                                        <i class="fas fa-vial mr-1"></i>{{ $record->lab_tests_count ?? 0 }} xét nghiệm
                                    </span>
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.medical-records.show', $record->id) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Xem chi tiết bệnh án & đơn thuốc">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($record->appointment_id)
                                    <a href="{{ route('admin.appointments.show', $record->appointment_id) }}" class="btn btn-primary btn-circle btn-sm" title="Xem phiếu lịch hẹn">
                                        <i class="fas fa-calendar-check"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy hồ sơ bệnh án nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($records, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $records->firstItem() ?? 0 }} - {{ $records->lastItem() ?? 0 }} trong tổng số {{ $records->total() }} bệnh án
                </div>
                <div>
                    {{ $records->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('exportAdminEmrCsvBtn')?.addEventListener('click', function () {
        var rows = [['STT', 'Mã bệnh án & Ngày', 'Bệnh nhân điều trị', 'Bác sĩ & Chẩn đoán lâm sàng']];
        document.querySelectorAll('table.table tbody tr').forEach(function (tr) {
            var cols = tr.querySelectorAll('td');
            if (cols.length >= 4) {
                rows.push([
                    cols[0].innerText.replace(/\s+/g, ' ').trim(),
                    cols[1].innerText.replace(/\s+/g, ' ').trim(),
                    cols[2].innerText.replace(/\s+/g, ' ').trim(),
                    cols[3].innerText.replace(/\s+/g, ' ').trim()
                ]);
            }
        });
        var csvContent = '\uFEFF' + rows.map(function (r) {
            return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'danh-sach-benh-an-emr-' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    });
</script>
@endpush
