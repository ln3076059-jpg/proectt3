@extends('layouts.admin')

@section('title', 'Thiết lập ca làm việc mới')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-calendar-plus text-primary mr-2"></i>Thiết lập ca làm việc & Tự động tạo khung giờ
    </h1>
    <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin ca làm việc & khung giờ tiếp nhận</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.schedules.store') }}" method="POST" id="scheduleForm">
                    @csrf

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Bác sĩ phụ trách ca <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                            <option value="">-- Chọn bác sĩ --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    BS. {{ $doc->user->name ?? 'N/A' }} ({{ $doc->department->name ?? 'Đa khoa' }})
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Chế độ thiết lập lịch: 1 ngày hoặc Hàng loạt theo khoảng ngày -->
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700 d-block">Chế độ thiết lập lịch trực</label>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-primary {{ old('schedule_mode', 'single') === 'single' ? 'active' : '' }}" id="lblModeSingle">
                                <input type="radio" name="schedule_mode" value="single" {{ old('schedule_mode', 'single') === 'single' ? 'checked' : '' }}>
                                <i class="far fa-calendar-check mr-1"></i> Theo 1 ngày đơn lẻ
                            </label>
                            <label class="btn btn-outline-primary {{ old('schedule_mode') === 'range' ? 'active' : '' }}" id="lblModeRange">
                                <input type="radio" name="schedule_mode" value="range" {{ old('schedule_mode') === 'range' ? 'checked' : '' }}>
                                <i class="fas fa-calendar-week mr-1"></i> Hàng loạt theo khoảng ngày (Từ ngày - Đến ngày)
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700" id="dateStartLabel">Ngày làm việc <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="startDateInput" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6" id="endDateCol" style="display: {{ old('schedule_mode') === 'range' ? 'block' : 'none' }};">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="font-weight-bold text-gray-700 mb-1">Đến ngày <span class="text-danger">*</span></label>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-light border py-0 px-2 small quick-range-btn" data-days="6">+7 ngày</button>
                                        <button type="button" class="btn btn-xs btn-light border py-0 px-2 small quick-range-btn" data-days="13">+14 ngày</button>
                                        <button type="button" class="btn btn-xs btn-light border py-0 px-2 small quick-range-btn" data-days="29">+30 ngày</button>
                                    </div>
                                </div>
                                <input type="date" name="end_date" id="endDateInput" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', date('Y-m-d', strtotime('+6 days'))) }}" min="{{ date('Y-m-d') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Chọn mẫu ca khám nhanh</label>
                                <select name="shift_preset" class="form-control" id="shiftPreset">
                                    <option value="custom" {{ old('shift_preset') === 'custom' ? 'selected' : '' }}>Tự chọn giờ</option>
                                    <option value="morning" {{ old('shift_preset', 'morning') === 'morning' ? 'selected' : '' }}>Ca sáng chuẩn (07:30 - 11:30)</option>
                                    <option value="afternoon" {{ old('shift_preset') === 'afternoon' ? 'selected' : '' }}>Ca chiều chuẩn (13:00 - 17:00)</option>
                                    <option value="fullday" {{ old('shift_preset') === 'fullday' ? 'selected' : '' }}>Cả ngày (Ca sáng 07:30-11:30 & Ca chiều 13:00-17:00)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tùy chọn các thứ trong tuần khi tạo lịch hàng loạt -->
                    <div class="form-group p-3 bg-light rounded border" id="daysOfWeekBox" style="display: {{ old('schedule_mode') === 'range' ? 'block' : 'none' }};">
                        <label class="font-weight-bold text-primary small text-uppercase d-block mb-2">
                            <i class="fas fa-calendar-check mr-1"></i> Chọn các thứ làm việc trong tuần (Áp dụng cho khoảng ngày đã chọn):
                        </label>
                        @php
                            $oldDays = old('days_of_week', [1, 2, 3, 4, 5, 6]);
                            $dayLabels = [
                                1 => 'Thứ 2',
                                2 => 'Thứ 3',
                                3 => 'Thứ 4',
                                4 => 'Thứ 5',
                                5 => 'Thứ 6',
                                6 => 'Thứ 7',
                                0 => 'Chủ nhật',
                            ];
                        @endphp
                        <div class="d-flex flex-wrap" style="gap: 14px;">
                            @foreach($dayLabels as $dayVal => $dayText)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="dow_{{ $dayVal }}" name="days_of_week[]" value="{{ $dayVal }}" {{ in_array($dayVal, $oldDays) ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-gray-800" for="dow_{{ $dayVal }}">{{ $dayText }}</label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-2">
                            * Hệ thống sẽ tự động bỏ qua các ca làm việc đã tồn tại trùng giờ trong khoảng ngày để tránh xung đột.
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Giờ bắt đầu <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', '07:30') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Giờ kết thúc <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', '11:30') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Độ dài mỗi khung giờ (phút) <span class="text-danger">*</span></label>
                                <select name="slot_duration" class="form-control @error('slot_duration') is-invalid @enderror">
                                    <option value="15" {{ old('slot_duration') == 15 ? 'selected' : '' }}>15 phút</option>
                                    <option value="20" {{ old('slot_duration') == 20 ? 'selected' : '' }}>20 phút</option>
                                    <option value="30" {{ old('slot_duration', 30) == 30 ? 'selected' : '' }}>30 phút (Khuyến nghị chuẩn)</option>
                                    <option value="45" {{ old('slot_duration') == 45 ? 'selected' : '' }}>45 phút</option>
                                    <option value="60" {{ old('slot_duration') == 60 ? 'selected' : '' }}>60 phút</option>
                                </select>
                                @error('slot_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Sức chứa mỗi khung giờ (`capacity`) <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', 5) }}" min="1" max="50" required>
                                <small class="text-muted">Số lượng bệnh nhân tiếp nhận tối đa trong 1 khung giờ</small>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Ghi chú ca trực (tùy chọn)</label>
                        <input type="text" name="note" class="form-control @error('note') is-invalid @enderror" value="{{ old('note') }}" placeholder="Ví dụ: Khám chuyên gia tại Phòng 201...">
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-magic"></i></span>
                        <span class="text">Tạo ca & Tự động sinh slot</span>
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Schedule & Slot Capacity Estimator -->
    <div class="col-lg-4">
        <div class="card shadow mb-4 border-left-info" id="liveScheduleEstimator">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-calculator mr-1"></i> Dự toán Tự động Khung Giờ &amp; Công Suất
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="small text-muted">Số khung giờ / ca:</span>
                    <strong class="text-primary h5 mb-0" id="estSlotsPerShift">-- slot</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="small text-muted">Tổng sức chứa / ca:</span>
                    <strong class="text-success h5 mb-0" id="estCapacityPerShift">-- bệnh nhân</strong>
                </div>
                <div class="small font-weight-bold text-gray-700 mb-2">
                    <i class="far fa-clock text-primary mr-1"></i> Xem trước các mốc khung giờ sẽ sinh:
                </div>
                <div id="estSampleSlotsPreview" class="d-flex flex-wrap" style="gap: 6px;"></div>
                <hr>
                <div class="small text-muted">
                    <i class="fas fa-shield-alt text-info mr-1"></i> Hệ thống tự động kiểm tra chống trùng lặp ca trực và khóa an toàn các slot đã có bệnh nhân đặt.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#shiftPreset').on('change', function() {
        var val = $(this).val();
        if (val === 'morning') {
            $('#start_time').val('07:30');
            $('#end_time').val('11:30');
        } else if (val === 'afternoon') {
            $('#start_time').val('13:00');
            $('#end_time').val('17:00');
        } else if (val === 'fullday') {
            $('#start_time').val('07:30');
            $('#end_time').val('17:00');
        }
    });

    function toggleScheduleMode() {
        var mode = $('input[name="schedule_mode"]:checked').val();
        if (mode === 'range') {
            $('#dateStartLabel').html('Từ ngày <span class="text-danger">*</span>');
            $('#endDateCol').slideDown(150);
            $('#daysOfWeekBox').slideDown(150);
            $('#endDateInput').prop('required', true);
        } else {
            $('#dateStartLabel').html('Ngày làm việc <span class="text-danger">*</span>');
            $('#endDateCol').slideUp(150);
            $('#daysOfWeekBox').slideUp(150);
            $('#endDateInput').prop('required', false);
        }
    }

    $('input[name="schedule_mode"]').on('change', toggleScheduleMode);
    toggleScheduleMode();

    $('.quick-range-btn').on('click', function() {
        var addDays = parseInt($(this).data('days'), 10);
        var startVal = $('#startDateInput').val();
        var baseDate = startVal ? new Date(startVal) : new Date();
        baseDate.setDate(baseDate.getDate() + addDays);
        var yyyy = baseDate.getFullYear();
        var mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        var dd = String(baseDate.getDate()).padStart(2, '0');
        $('#endDateInput').val(yyyy + '-' + mm + '-' + dd);
    });

    function updateLiveScheduleEstimator() {
        var startStr = $('#start_time').val() || '07:30';
        var endStr = $('#end_time').val() || '11:30';
        var duration = parseInt($('select[name="slot_duration"]').val(), 10) || 30;
        var cap = parseInt($('input[name="capacity"]').val(), 10) || 5;

        var sParts = startStr.split(':');
        var eParts = endStr.split(':');
        if (sParts.length < 2 || eParts.length < 2) return;

        var startMin = parseInt(sParts[0], 10) * 60 + parseInt(sParts[1], 10);
        var endMin = parseInt(eParts[0], 10) * 60 + parseInt(eParts[1], 10);

        if (endMin <= startMin || duration <= 0) {
            $('#estSlotsPerShift').text('0 slot');
            $('#estCapacityPerShift').text('0 bệnh nhân');
            $('#estSampleSlotsPreview').html('<span class="text-danger small">Giờ kết thúc phải lớn hơn giờ bắt đầu.</span>');
            return;
        }

        var slots = [];
        var cur = startMin;
        while (cur + duration <= endMin) {
            var h1 = String(Math.floor(cur / 60)).padStart(2, '0');
            var m1 = String(cur % 60).padStart(2, '0');
            var next = cur + duration;
            var h2 = String(Math.floor(next / 60)).padStart(2, '0');
            var m2 = String(next % 60).padStart(2, '0');
            slots.push(h1 + ':' + m1 + ' - ' + h2 + ':' + m2);
            cur = next;
        }

        $('#estSlotsPerShift').text(slots.length + ' khung giờ');
        $('#estCapacityPerShift').text((slots.length * cap) + ' bệnh nhân');
        var badgesHtml = slots.slice(0, 12).map(function(s) {
            return '<span class="badge badge-light border text-gray-800 px-2 py-1">' + s + '</span>';
        }).join('');
        if (slots.length > 12) {
            badgesHtml += '<span class="badge badge-secondary px-2 py-1">+' + (slots.length - 12) + ' khung giờ khác</span>';
        }
        $('#estSampleSlotsPreview').html(badgesHtml);
    }

    $('#start_time, #end_time, select[name="slot_duration"], input[name="capacity"], #shiftPreset').on('input change', updateLiveScheduleEstimator);
    updateLiveScheduleEstimator();
});
</script>
@endpush
