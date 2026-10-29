<!-- SB Admin 2 Search Card Component for Appointments -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-search mr-1"></i> Tra cứu danh sách lịch hẹn khám
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.appointments.index') }}" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">Tên bệnh nhân</label>
                <input type="text" name="patient_name" value="{{ request('patient_name') }}" placeholder="Nhập tên bệnh nhân" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">Bác sĩ phụ trách</label>
                <input type="text" name="doctor_name" value="{{ request('doctor_name') }}" placeholder="Nhập tên bác sĩ" class="form-control">
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">Ngày khám</label>
                <input type="date" name="appointment_date" value="{{ request('appointment_date') }}" class="form-control">
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã khám</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-filter mr-1"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>
</div>
