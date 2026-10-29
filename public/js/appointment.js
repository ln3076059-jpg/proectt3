document.addEventListener('DOMContentLoaded', () => {
    const serviceSelect = document.getElementById('service_id');
    const departmentSelect = document.getElementById('department_id');
    const doctorSelect = document.getElementById('doctor_id');
    const examSelect = document.getElementById('medical_examination');
    const apptTimeInput = document.getElementById('appointment_time');
    const form = document.getElementById('appointmentForm');
    const dateInput = document.getElementById('appointment_date');

    // Hiển thị chi tiết gói dịch vụ
    const sdEmpty = document.getElementById('sd_empty');
    const sdContent = document.getElementById('sd_content');
    const sdName = document.getElementById('sd_name');
    const sdPrice = document.getElementById('sd_price');
    const sdDept = document.getElementById('sd_dept');
    const sdDesc = document.getElementById('sd_desc');
    const sdImage = document.getElementById('sd_image');

    // 🔹 Giới hạn ngày đặt lịch trong 7 ngày (cả cuối tuần)
    // Đảm bảo không cho chọn ngày đã qua (tự động cập nhật khi qua ngày mới)
    if (dateInput) {
        // Hàm lấy ngày hôm nay theo local time (tránh vấn đề timezone)
        function getTodayString() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Hàm lấy ngày tối đa (6 ngày sau)
        function getMaxDateString() {
            const today = new Date();
            const maxDate = new Date(today);
            maxDate.setDate(today.getDate() + 6);
            const year = maxDate.getFullYear();
            const month = String(maxDate.getMonth() + 1).padStart(2, '0');
            const day = String(maxDate.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Cập nhật min và max
        const todayStr = getTodayString();
        const maxDateStr = getMaxDateString();
        
        dateInput.min = todayStr;
        dateInput.max = maxDateStr;

        // Nếu người dùng đã chọn ngày trong quá khứ, tự động reset về hôm nay
        if (dateInput.value && dateInput.value < todayStr) {
            dateInput.value = todayStr;
        }

        // Thêm event listener để kiểm tra khi người dùng thay đổi ngày
        dateInput.addEventListener('change', function() {
            const selectedDate = this.value;
            const currentToday = getTodayString();
            
            // Nếu chọn ngày trong quá khứ, tự động reset về hôm nay
            if (selectedDate && selectedDate < currentToday) {
                alert('Không thể chọn ngày trong quá khứ. Đã tự động chuyển về ngày hôm nay.');
                this.value = currentToday;
            }
        });

        // Kiểm tra lại mỗi khi focus vào input (để cập nhật khi qua ngày mới)
        dateInput.addEventListener('focus', function() {
            const currentToday = getTodayString();
            const currentMax = getMaxDateString();
            
            // Cập nhật lại min và max
            this.min = currentToday;
            this.max = currentMax;
            
            // Nếu giá trị hiện tại là ngày cũ, reset về hôm nay
            if (this.value && this.value < currentToday) {
                this.value = currentToday;
            }
        });
    }

    // 🔒 Khóa chọn khoa (và giữ nguyên hành vi hiện tại) nếu chưa đăng nhập
    const isAuth = form && form.dataset && form.dataset.auth === '1';
    if (!isAuth && departmentSelect) {
        departmentSelect.disabled = true;
    }

    // ============================================================
    // 🔥 HÀM CHÍNH: cập nhật chi tiết gói dịch vụ
    // ============================================================
    function updateServiceDetails() {
        const opt = serviceSelect.options[serviceSelect.selectedIndex];

        if (!opt || !opt.value) {
            if (sdEmpty && sdContent) {
                sdEmpty.classList.remove('d-none');
                sdContent.classList.add('d-none');
            }
            return;
        }

        // 🔹 Tự động gán khoa theo dịch vụ (nếu chưa chọn khoa hoặc khác khoa)
        const deptId = opt.dataset.departmentId;
        if (deptId) {
            if (!departmentSelect.value || departmentSelect.value !== deptId) {
                departmentSelect.value = deptId;
            }

            // Lọc bác sĩ theo khoa
            doctorSelect.querySelectorAll('option').forEach(o => {
                o.style.display = (o.dataset.departmentId === deptId || o.value === '') ? '' : 'none';
            });
            doctorSelect.value = '';
        }

        // 🔹 Tên và giá dịch vụ
        const nameText = opt.textContent ? opt.textContent.split('(')[0].trim() : '';
        const price = opt.dataset.price ? Number(opt.dataset.price) : null;

        if (sdName) sdName.textContent = nameText;
        if (sdPrice) {
            sdPrice.textContent = price && !Number.isNaN(price)
                ? price.toLocaleString('vi-VN') + ' đ'
                : '-';
        }

        // 🔹 Ảnh dịch vụ
        if (sdImage) {
            const imageUrl = opt.dataset.image || '';
            if (imageUrl) {
                sdImage.src = imageUrl;
                sdImage.style.display = 'block';
            } else {
                sdImage.src = '';
                sdImage.style.display = 'none';
            }
        }

        // 🔹 Tên khoa
        const deptOpt = departmentSelect.options[departmentSelect.selectedIndex];
        if (sdDept) sdDept.textContent = deptOpt ? deptOpt.textContent : '-';

        // 🔹 Mô tả gói → hiển thị thành danh sách
        if (sdDesc) {
            const desc = opt.dataset.description || '';
            const lines = desc
                .split(/\r\n|\r|\n/)
                .map(l => l.trim())
                .filter(l => l.length > 0);

            if (lines.length) {
                const ul = document.createElement('ul');
                ul.className = 'mb-0';
                lines.forEach(text => {
                    const li = document.createElement('li');
                    li.textContent = text;
                    ul.appendChild(li);
                });
                sdDesc.innerHTML = '';
                sdDesc.appendChild(ul);
            } else {
                sdDesc.textContent = '-';
            }
        }

        // 🔹 Hiển thị box thông tin dịch vụ
        if (sdEmpty && sdContent) {
            sdEmpty.classList.add('d-none');
            sdContent.classList.remove('d-none');
        }
    }

    // ============================================================
    // Sự kiện
    // ============================================================

    // Khi chọn khoa → lọc dịch vụ và bác sĩ
    departmentSelect.addEventListener('change', () => {
        const deptId = departmentSelect.value;

        // Lọc dịch vụ theo khoa
        serviceSelect.querySelectorAll('option').forEach(o => {
            if (!o.value) {
                o.style.display = '';
                return;
            }
            const sDeptId = o.dataset.departmentId;
            o.style.display = (!deptId || sDeptId === deptId) ? '' : 'none';
        });
        serviceSelect.value = '';

        // Lọc bác sĩ theo khoa
        doctorSelect.querySelectorAll('option').forEach(o => {
            if (!o.value) {
                o.style.display = '';
                return;
            }
            const dDeptId = o.dataset.departmentId;
            o.style.display = (!deptId || dDeptId === deptId) ? '' : 'none';
        });
        doctorSelect.value = '';

        // Reset thông tin dịch vụ
        if (sdEmpty && sdContent) {
            sdEmpty.classList.remove('d-none');
            sdContent.classList.add('d-none');
        }
    });

    // Khi chọn dịch vụ → cập nhật thông tin
    serviceSelect.addEventListener('change', updateServiceDetails);

    // Khi chọn ca khám → gán giờ khám
    examSelect.addEventListener('change', () => {
        apptTimeInput.value = examSelect.value;
    });

    // Validate form trước khi submit
    form.addEventListener('submit', e => {
        const required = form.querySelectorAll('[required]');
        let valid = true;

        required.forEach(f => {
            if (!f.value.trim()) {
                valid = false;
                f.classList.add('is-invalid');
            } else {
                f.classList.remove('is-invalid');
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Vui lòng nhập đầy đủ thông tin bắt buộc.');
        }
    });

    // Tự cập nhật lần đầu khi trang load
    const hasPreselectedService = !!serviceSelect.value;
    const hasPreselectedDept = !!departmentSelect.value;

    if (hasPreselectedDept && !hasPreselectedService) {
        // Nếu đã có khoa được chọn sẵn nhưng chưa chọn dịch vụ → lọc options theo khoa
        const event = new Event('change');
        departmentSelect.dispatchEvent(event);
    }

    // Nếu có dịch vụ được chọn sẵn → cập nhật chi tiết và đồng bộ khoa, bác sĩ
    updateServiceDetails();
});
