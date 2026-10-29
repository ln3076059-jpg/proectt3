@extends('layouts.doctor')

@section('title', 'Lịch sử khám bệnh & Bệnh án EMR')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-history text-primary mr-2"></i>Lịch sử khám bệnh & Hồ sơ bệnh án điện tử (EMR)
        </h1>
        <p class="text-muted small mb-0">Theo dõi toàn bộ quá trình chẩn đoán, kê đơn thuốc và phác đồ điều trị cho bệnh nhân</p>
    </div>
    <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 8px;">
        <button type="button" id="exportEmrCsvBtn" class="btn btn-sm btn-outline-success shadow-sm">
            <i class="fas fa-file-csv fa-sm mr-1"></i> Xuất CSV bệnh án
        </button>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-primary shadow-sm">
            <i class="fas fa-print fa-sm mr-1"></i> In sổ khám
        </button>
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại Dashboard
        </a>
    </div>
</div>

{{-- Flash Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
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
        <form action="{{ route('doctor.patient.history') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-6 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TÌM KIẾM</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên bệnh nhân, tên người thân, SĐT, mã lịch hẹn hoặc chẩn đoán...">
            </div>
            <div class="col-md-3 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="font-weight-bold text-gray-700 small mb-0">NGÀY KHÁM</label>
                    <a href="{{ route('doctor.patient.history', ['date' => now()->toDateString(), 'search' => request('search')]) }}" class="badge badge-info text-decoration-none">Hôm nay</a>
                </div>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                    <i class="fas fa-search mr-1"></i> Tìm kiếm
                </button>
                @if(request('search') || request('date'))
                    <a href="{{ route('doctor.patient.history') }}" class="btn btn-outline-secondary" title="Xóa lọc">
                        <i class="fas fa-redo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Bảng Lịch sử khám bệnh -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-file-medical-alt mr-1"></i> Danh sách ca bệnh đã tiếp nhận & chẩn đoán
        </h6>
        <span class="badge badge-primary px-3 py-2">Tổng số: {{ $medicalRecords->total() ?? $medicalRecords->count() }} hồ sơ</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered no-datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">STT & Mã</th>
                        <th>Bệnh nhân & Thông tin cá nhân</th>
                        <th>Thời gian khám & Dịch vụ y tế</th>
                        <th>Chẩn đoán lâm sàng & Đơn thuốc</th>
                        <th class="text-center" style="width: 140px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicalRecords as $record)
                        @php
                            $appointment = $record->appointment;
                            $patient = $appointment ? $appointment->patient : $record->patient;
                            $isFamily = $appointment && $appointment->booking_for === 'relative';
                            $patientName = $appointment ? $appointment->patient_display_name : ($patient->name ?? 'Bệnh nhân');
                        @endphp
                        <tr>
                            <!-- Cột 1: STT & Mã bệnh án -->
                            <td class="text-center align-middle">
                                <span class="font-weight-bold text-gray-700">#{{ $loop->iteration }}</span>
                                <div class="mt-1">
                                    <span class="badge badge-secondary">
                                        #EMR-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Cột 2: Bệnh nhân -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-user mr-1 text-info"></i>{{ $patientName }}
                                    @if($isFamily)
                                        <span class="badge badge-warning text-dark ml-1">Người thân khám</span>
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">
                                    @if($patient && $patient->birthdate)
                                        <span>Ngày sinh: {{ \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') }}</span> |
                                    @endif
                                    <span>SĐT: <strong class="text-gray-800">{{ $patient->phone ?? '0912345678' }}</strong></span>
                                </div>
                            </td>

                            <!-- Cột 3: Thời gian khám & Dịch vụ -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    <i class="fas fa-stethoscope text-primary mr-1"></i>
                                    {{ $appointment->service->name ?? 'Khám chuyên khoa' }}
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="far fa-calendar-alt mr-1 text-info"></i>
                                    {{ $record->created_at ? $record->created_at->format('d/m/Y H:i') : ($appointment ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') : '—') }}
                                </div>
                            </td>

                            <!-- Cột 4: Chẩn đoán lâm sàng -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-danger">
                                    {{ $record->diagnosis ?? 'Đang theo dõi lâm sàng' }}
                                </div>
                                @if($record->description)
                                    <div class="small text-gray-600 mt-1">
                                        Triệu chứng: {{ Str::limit($record->description, 60) }}
                                    </div>
                                @endif
                                @if($record->prescriptionItems && $record->prescriptionItems->count() > 0)
                                    <div class="mt-1 d-flex flex-wrap align-items-center" style="gap: 4px;">
                                        <span class="badge badge-success">
                                            <i class="fas fa-pills mr-1"></i>{{ $record->prescriptionItems->count() }} thuốc:
                                        </span>
                                        @foreach($record->prescriptionItems->take(3) as $pi)
                                            <span class="badge badge-light border text-dark">
                                                {{ optional($pi->medicine)->name ?? 'Thuốc' }} ({{ $pi->quantity }})
                                            </span>
                                        @endforeach
                                        @if($record->prescriptionItems->count() > 3)
                                            <span class="badge badge-light border text-muted">+{{ $record->prescriptionItems->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                @if($appointment)
                                    <a href="{{ route('doctor.patient.record', $appointment) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Xem chi tiết bệnh án & đơn thuốc">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('doctor.patient.record', $appointment) }}" class="btn btn-primary btn-circle btn-sm" title="Khám & Chỉnh sửa bệnh án">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-2x mb-2 text-gray-400 d-block"></i>
                                Chưa có hồ sơ bệnh án nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($medicalRecords, 'links'))
            <div class="mt-3 d-flex justify-content-end">
                {{ $medicalRecords->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('exportEmrCsvBtn')?.addEventListener('click', function () {
        const rows = [['Mã bệnh án', 'Bệnh nhân', 'Dịch vụ & Thời gian', 'Chẩn đoán & Đơn thuốc']];
        document.querySelectorAll('table.table tbody tr').forEach(function (tr) {
            const cols = tr.querySelectorAll('td');
            if (cols.length >= 4) {
                rows.push([
                    cols[0].innerText.replace(/\s+/g, ' ').trim(),
                    cols[1].innerText.replace(/\s+/g, ' ').trim(),
                    cols[2].innerText.replace(/\s+/g, ' ').trim(),
                    cols[3].innerText.replace(/\s+/g, ' ').trim()
                ]);
            }
        });
        const csvContent = '\uFEFF' + rows.map(r => r.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'lich-su-benh-an-emr-' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    });
</script>
@endpush
