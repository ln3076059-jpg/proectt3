
// Hiển thị thông báo đẹp hơn
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '1080';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service');
    const departmentSelect = document.getElementById('department');
    const doctorSelect = document.getElementById('doctor');

    // Khi chọn dịch vụ -> tự động gán khoa
    serviceSelect?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const departmentId = selectedOption.getAttribute('data-department-id');
        if (departmentId) {
            departmentSelect.value = departmentId;
            departmentSelect.dispatchEvent(new Event('change'));
        }
    });

    // Khi chọn khoa -> lọc danh sách bác sĩ
    departmentSelect?.addEventListener('change', function() {
        const departmentId = this.value;
        const doctorOptions = document.querySelectorAll('.doctor-option');

        doctorSelect.innerHTML = '<option value="">-- Vui lòng chọn bác sĩ --</option>';

        if (departmentId) {
            const filteredDoctors = Array.from(doctorOptions).filter(opt => opt.dataset.departmentId === departmentId);
            if (filteredDoctors.length > 0) {
                filteredDoctors.forEach(opt => {
                    opt.style.display = '';
                    doctorSelect.appendChild(opt.cloneNode(true));
                });
            } else {
                doctorSelect.innerHTML = '<option value="">Không có bác sĩ nào trong khoa này</option>';
            }
        }
    });

    // Xử lý gửi form
    document.getElementById('appointmentForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const patientId = document.getElementById('patient_id')?.value;
        if (!patientId) {
            showAlert('Không tìm thấy thông tin bệnh nhân. Vui lòng liên hệ quản trị viên.', 'danger');
            return;
        }

        const requiredFields = ['service_id', 'doctor_id', 'appointment_date', 'appointment_time'];
        let missingFields = [];

        requiredFields.forEach(field => {
            const element = this.querySelector(`[name="${field}"]`);
            if (element && !element.value) {
                missingFields.push(field);
                element.classList.add('is-invalid');
            } else if (element) {
                element.classList.remove('is-invalid');
            }
        });

        if (missingFields.length > 0) {
            showAlert('Vui lòng điền đầy đủ thông tin bắt buộc.', 'warning');
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

        try {
            const formData = new FormData(this);
            const appointmentTime = formData.get('appointment_time') === 'morning'
                ? '07:30 - 11:30'
                : '13:00 - 17:00';

            const appointmentData = {
                patient_id: formData.get('patient_id'),
                service_id: formData.get('service_id'),
                doctor_id: formData.get('doctor_id'),
                appointment_date: formData.get('appointment_date'),
                appointment_time: appointmentTime,
                status: 'pending',
                note: formData.get('note') || ''
            };

            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(appointmentData)
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Có lỗi xảy ra');

            if (data.success) {
                showAlert('Đặt lịch thành công! Vui lòng chờ xác nhận.', 'success');
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('appointmentModal'));
                    modal?.hide();
                    this.reset();
                    doctorSelect.innerHTML = '<option value="">-- Vui lòng chọn khoa trước --</option>';
                    if (data.redirect_url) window.location.href = data.redirect_url;
                }, 1500);
            } else {
                showAlert('Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.', 'danger');
            }
        } catch (error) {
            console.error('Lỗi khi gửi yêu cầu:', error);
            showAlert('Đã xảy ra lỗi khi gửi yêu cầu.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });

    // Modal setup (không backdrop)
    const modalElement = document.getElementById('appointmentModal');
    if (modalElement) {
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: false,
            keyboard: true,
            focus: true
        });
        const bookBtn = document.querySelector('[data-bs-target="#appointmentModal"]');
        bookBtn?.addEventListener('click', e => {
            e.preventDefault();
            modal.show();
        });
        document.addEventListener('shown.bs.modal', () => {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        });
    }
});
