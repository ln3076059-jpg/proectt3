# 🏥 HỆ THỐNG ĐẶT LỊCH KHÁM & QUẢN LÝ PHÒNG KHÁM ĐA KHOA THÁI THỊNH
*(Tài liệu Hướng dẫn Chạy Dự án, Tài khoản Đăng nhập Mẫu & Kịch bản Demo Đồ án)*

---

## 🚀 1. CÁCH CHẠY DỰ ÁN NHANH (WINDOWS / POWERSHELL)

### Bước 1: Mở Terminal (PowerShell) tại thư mục gốc của dự án
```powershell
cd "d:\.idea\it63thang9\thái"
```

### Bước 2: Khởi tạo Database & Nạp dữ liệu mẫu chuẩn (`ThaiThinhClinicDemoSeeder`)
> **Lưu ý:** Đảm bảo dịch vụ **MySQL** (XAMPP / Laragon / MySQL Server) đang chạy ở cổng `3306` với tên database trong file `.env` là `phongkham_thaithinh` (hoặc hệ thống sẽ tự kết nối theo cấu hình `.env`).

- **Nếu máy đã cấu hình sẵn `php` trong PATH:**
  ```powershell
  php artisan migrate:fresh --seed
  ```
- **Nếu chạy trên máy hiện tại (sử dụng PHP 8.3 cài qua WinGet):**
  ```powershell
  & "C:\Users\TSC\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan migrate:fresh --seed
  ```

### Bước 3: Khởi chạy Web Server Laravel
- **Cách 1 — Chạy ở cổng mặc định `8000` (hoặc cổng `8095`):**
  ```powershell
  php artisan serve --host=127.0.0.1 --port=8000
  ```
- **Cách 2 — Lệnh chạy trực tiếp bằng đường dẫn PHP 8.3 trên máy hiện tại:**
  ```powershell
  & "C:\Users\TSC\AppData\Local\Microsoft\\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8095
  ```
  *(Hiện tại server đang được bật sẵn tại địa chỉ: **`http://127.0.0.1:8095`** — bạn có thể mở trình duyệt truy cập ngay!)*

### Bước 4: Các đường dẫn (URL) truy cập nhanh
*(Thay `8095` bằng `8000` nếu bạn chạy ở cổng `8000`)*

| Phân hệ / Chức năng | Đường dẫn truy cập (URL) |
| :--- | :--- |
| **Trang chủ Phòng khám** | `http://127.0.0.1:8095/` |
| **Chuyên gia Tư vấn Triệu chứng AI** | `http://127.0.0.1:8095/tu-van-trieu-chung` |
| **Danh sách Bác sĩ & Lịch trống thực tế** | `http://127.0.0.1:8095/doctors` |
| **Danh sách Chuyên khoa & Dịch vụ** | `http://127.0.0.1:8095/departments` / `http://127.0.0.1:8095/services` |
| **Cổng Đăng nhập Bệnh nhân** | `http://127.0.0.1:8095/patient/login` *(hoặc `/login`)* |
| **Cổng Đăng nhập Bác sĩ** | `http://127.0.0.1:8095/doctor/login` |
| **Cổng Đăng nhập Quản trị viên / Lễ tân** | `http://127.0.0.1:8095/admin/login` |

---

## 🔐 2. THÔNG TIN TÀI KHOẢN ĐĂNG NHẬP MẪU (DEMO ACCOUNTS)

> 💡 **Mẹo thao tác nhanh:** Trên tất cả các trang đăng nhập (`/patient/login`, `/doctor/login`, `/admin/login`), hệ thống đã tích hợp sẵn **Nút chọn nhanh tài khoản Demo (1-Click Auto-fill)**. Bạn chỉ cần bấm vào nút tài khoản tương ứng là Email & Mật khẩu sẽ tự động được điền sẵn!

### 2.1. Tài khoản Quản trị viên & Lễ tân (Admin / Reception / CSKH)
- **URL đăng nhập:** `/admin/login`

| Vai trò | Họ và tên | Email đăng nhập | Mật khẩu | Quyền hạn & Chức năng chính |
| :--- | :--- | :--- | :--- | :--- |
| **Admin / Lễ tân** | **Quản trị viên Thái Thịnh** | `admin@thaithinh.vn` | `admin123` | • Tiếp đón & **Check-in QR 1-Click** ngay trên Dashboard<br>• Xem 3 thẻ **Cảnh báo vận hành** (Hoàn tiền, Xét nghiệm chờ, Thuốc sắp hết)<br>• Duyệt / Hủy / Hoàn tiền lịch hẹn & Xuất báo cáo CSV<br>• Quản lý Bác sĩ, Lịch trực (có bộ ước tính ca khám tự động), Chuyên khoa, Dịch vụ<br>• Quản lý Kho thuốc (4 thẻ KPI lọc tồn kho), Danh mục bệnh (ICD), Cập nhật kết quả Xét nghiệm<br>• Tư vấn trực tuyến (Live Chat CSKH kèm ngữ cảnh lịch hẹn bệnh nhân) |

---

### 2.2. Tài khoản Bác sĩ Chuyên khoa (4 Bác sĩ)
- **URL đăng nhập:** `/doctor/login`
- **Mật khẩu chung cho tất cả bác sĩ:** `doctor123`

| STT | Bác sĩ | Chuyên khoa & Phòng khám | Email đăng nhập | Mật khẩu | Lịch trực & Dữ liệu mẫu nổi bật |
| :---: | :--- | :--- | :--- | :--- | :--- |
| 1 | **BS. CKII Nguyễn Văn Hùng** | **Khoa Tim mạch**<br>*(Phòng 201 - Tầng 2)* | `hung.nguyen@thaithinh.vn` | `doctor123` | • **Khuyên dùng để Demo chính**<br>• Có sẵn ca khám **Hôm nay** (chờ khám & đã check-in) và **Ngày mai**<br>• Có sẵn bệnh nhân cũ với đầy đủ Bệnh án điện tử, Đơn thuốc & Xét nghiệm để test tính năng **Sao chép đơn thuốc cũ 1-Click** |
| 2 | **ThS. BS Trần Thị Mai** | **Khoa Tai Mũi Họng**<br>*(Phòng 302 - Tầng 3)* | `mai.tran@thaithinh.vn` | `doctor123` | • Lịch trực Thứ 2 → Thứ 7 (08:00 - 16:30)<br>• Có sẵn ca khám đã check-in hôm nay và lịch sử khám Tai Mũi Họng |
| 3 | **BSCK I Lê Hoàng Nam** | **Khoa Nhi**<br>*(Phòng 105 - Tầng 1)* | `nam.le@thaithinh.vn` | `doctor123` | • Lịch trực Thứ 2 → Thứ 6 (08:00 - 17:00)<br>• Chuyên khám Nhi khoa, có sẵn lịch hẹn ngày mai |
| 4 | **BSCK II Phạm Thị Thu Hương** | **Khoa Nội tổng quát**<br>*(Phòng 204 - Tầng 2)* | `huong.pham@thaithinh.vn` | `doctor123` | • Lịch trực Thứ 2 → Thứ 6 (07:30 - 16:30)<br>• Chuyên khám Nội tổng quát, tầm soát sức khỏe định kỳ |

---

### 2.3. Tài khoản Bệnh nhân Mẫu (6 Bệnh nhân)
- **URL đăng nhập:** `/patient/login` *(hoặc `/login`)*
- **Mật khẩu chung cho tất cả bệnh nhân:** `demo123`

| STT | Họ và tên Bệnh nhân | Email đăng nhập | Mật khẩu | Số điện thoại | Dữ liệu mẫu có sẵn để test ngay |
| :---: | :--- | :--- | :--- | :--- | :--- |
| 1 | **Bùi Tuấn Anh** | `patient1@thaithinh.vn` | `demo123` | `0987654321` | • **Khuyên dùng để Demo chính**<br>• Có 01 lịch hẹn **Hôm nay (09:00)** với BS. Hùng (trạng thái *Chờ khám* — dùng để lấy mã QR test Check-in)<br>• Có 01 lịch hẹn **Đã hoàn thành** (3 ngày trước) kèm **Bệnh án điện tử, Chỉ số sinh hiệu, Kết quả xét nghiệm máu, Đơn thuốc 4 buổi & Lịch hẹn tái khám** |
| 2 | **Vũ Thị Hồng Nhung** | `patient2@thaithinh.vn` | `demo123` | `0912345678` | • Có 01 lịch hẹn **Hôm nay (10:00)** với BS. Hùng (trạng thái **Đã check-in** — đang đứng đầu hàng đợi ưu tiên tại màn hình Bác sĩ)<br>• Có 01 hồ sơ bệnh án cũ tại Khoa Tai Mũi Họng |
| 3 | **Nguyễn Minh Quang** | `patient3@thaithinh.vn` | `demo123` | `0934567890` | • Có 01 lịch hẹn **Hôm nay (08:30)** với BS. Mai (trạng thái **Đã check-in**) |
| 4 | **Lê Thu Trang** | `patient4@thaithinh.vn` | `demo123` | `0976543210` | • Có 01 lịch hẹn **Ngày mai (09:00)** tại Khoa Nhi (BS. Nam) |
| 5 | **Hoàng Đức Trọng** | `patient5@thaithinh.vn` | `demo123` | `0908765432` | • Có 01 lịch hẹn **Ngày mai (14:00)** tại Khoa Nội tổng quát (BS. Hương) |
| 6 | **Đỗ Phương Linh** | `patient6@thaithinh.vn` | `demo123` | `0965432109` | • Có 01 lịch hẹn **Ngày mai (10:30)** tại Khoa Tim mạch (BS. Hùng) — trạng thái *Chưa thanh toán* (dùng để test thanh toán lại qua VNPay) |

---

### 2.4. Thông tin Thẻ ATM Nội địa mẫu để test Thanh toán VNPay Sandbox
Khi đặt lịch khám mới hoặc thanh toán lịch hẹn qua cổng **VNPay Sandbox**, chọn phương thức **"Thẻ nội địa và tài khoản ngân hàng"** -> chọn ngân hàng **NCB** và nhập thông tin sau:

| Thông tin thẻ | Giá trị nhập |
| :--- | :--- |
| **Ngân hàng** | `NCB` (Ngân hàng Quốc Dân) |
| **Số thẻ** | `9704198526191432198` |
| **Tên chủ thẻ** | `NGUYEN VAN A` |
| **Ngày phát hành** | `07/15` |
| **Mật khẩu OTP** | `123456` |

---

## 🧪 3. CÁCH CHẠY BỘ KIỂM THỬ TỰ ĐỘNG (AUTOMATED TESTS & STRESS TEST)

Dự án được trang bị bộ kiểm thử tự động kiểm tra toàn diện tất cả các phân hệ và kiểm thử chịu tải chống trùng lịch (Race Condition):

### 3.1. Kiểm thử toàn bộ 7 Phân hệ & Chức năng con (197 Assertions — Không làm mất dữ liệu mẫu)
File test [`tests/Feature/AllModulesAndSubFunctionsTest.php`](file:///d:/.idea/it63thang9/thái/tests/Feature/AllModulesAndSubFunctionsTest.php) sử dụng `DatabaseTransactions` nên **giữ nguyên toàn bộ dữ liệu mẫu** sau khi chạy:
```powershell
# Nếu đã có php trong PATH:
php artisan test tests/Feature/AllModulesAndSubFunctionsTest.php

# Hoặc chạy bằng đường dẫn PHP 8.3 trên máy hiện tại:
& "C:\Users\TSC\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan test tests/Feature/AllModulesAndSubFunctionsTest.php
```

### 3.2. Kiểm thử Chịu tải 20 Luồng đặt lịch đồng thời (Concurrency / Race Condition Test)
Mô phỏng **20 bệnh nhân bấm đặt cùng 1 khung giờ trong cùng 1 mili-giây** để chứng minh cơ chế `DB::transaction` + `lockForUpdate()` chỉ cho phép đúng 1 người đặt thành công và chặn 19 người còn lại:
```powershell
php artisan test:concurrency
```

### 3.3. Chạy toàn bộ Test Suite của dự án
> ⚠️ **Lưu ý:** File `AppointmentWorkflowTest.php` sử dụng `RefreshDatabase`, nên sau khi chạy lệnh `php artisan test` toàn cục, bạn hãy chạy lại `php artisan migrate:fresh --seed` để khôi phục dữ liệu mẫu cho trình duyệt.

---

## 🎯 4. KỊCH BẢN DEMO ĐỒ ÁN TOÀN DIỆN (GỢI Ý TRÌNH BÀY TRƯỚC HỘI ĐỒNG)

### Phần 1: Trải nghiệm Bệnh nhân & Hệ chuyên gia Tư vấn Triệu chứng AI
1. Truy cập **Trang chủ (`/`)**:
   - Gõ thử vào ô tìm kiếm thông minh (`#heroSearchInput`) từ khóa như `"tim mạch"`, `"BS Hùng"`, hoặc `"siêu âm"` để xem **Dropdown gợi ý tức thì (Instant Autocomplete)** phân nhóm theo Chuyên khoa, Bác sĩ, Dịch vụ.
2. Truy cập **Chuyên gia Tư vấn Triệu chứng (`/tu-van-trieu-chung`)**:
   - Chọn nhóm đối tượng (*Người lớn / Trẻ em / Người cao tuổi / Phụ nữ mang thai*).
   - Nhập câu tự nhiên, ví dụ: *"Tôi bị tức ngực khó thở và tim đập nhanh từ hôm qua"*.
   - Hệ thống tự động hiển thị **Cảnh báo Triệu chứng Cấp cứu (Red-Flag Triage Alert)** ngay lập tức, đồng thời sau khi bấm phân tích sẽ gợi ý **Khoa Tim mạch**, các bệnh lý nghi ngờ kèm độ khớp (`%`), và **Danh sách Bác sĩ có lịch trống thực tế gần nhất** để đặt lịch 1-Click.
3. Đăng nhập tài khoản Bệnh nhân **`patient1@thaithinh.vn` (`demo123`)**:
   - Vào **Lịch hẹn của tôi (`/appointments`)**: Bấm nút **Mã QR** để mở Modal QR Check-in nhanh, xem **Thứ tự chờ thực tế trước mặt (`$queueAheadCount`)** và nút **Thêm nhắc lịch Google Calendar**.
   - Vào **Bệnh án điện tử** của ca khám đã hoàn thành: Xem **Banner Nhắc lịch tái khám thông minh** (tự động bóc tách ngày tái khám từ lời dặn bác sĩ), **Huy hiệu Chỉ số sinh hiệu**, và **Lịch uống thuốc 4 buổi (Sáng / Trưa / Chiều / Tối)** trực quan.

### Phần 2: Lễ tân Tiếp đón QR 1-Click & Quản trị Vận hành (`/admin/login`)
1. Đăng nhập Admin **`admin@thaithinh.vn` (`admin123`)**.
2. Tại **Dashboard (`/admin/dashboard`)**:
   - Quan sát **3 Thẻ Cảnh báo Vận hành** (Lịch hủy chờ hoàn tiền, Xét nghiệm chờ trả kết quả, Thuốc sắp hết/hết hàng).
   - Nhập mã phiếu khám (ví dụ `#1`) vào **Thanh Check-in QR 1-Click** ngay đầu Dashboard để chuyển trạng thái bệnh nhân sang `checkedin` và cấp số thứ tự vào phòng khám.
3. Vào **Quản lý Xét nghiệm (`/admin/lab-tests`)**: Bấm cập nhật nhanh kết quả xét nghiệm ngay trên Modal mà không cần rời trang.
4. Vào **Tạo lịch trực Bác sĩ (`/admin/schedules/create`)**: Thay đổi giờ bắt đầu/kết thúc để xem **Bộ ước tính tự động số khung giờ & công suất phục vụ tối đa**.

### Phần 3: Bác sĩ Khám bệnh Chuyên sâu & Kê đơn Thông minh (`/doctor/login`)
1. Đăng nhập Bác sĩ **`hung.nguyen@thaithinh.vn` (`doctor123`)**.
2. Tại **Dashboard Bác sĩ (`/doctor/dashboard`)**:
   - Xem **Hàng đợi Ưu tiên Bệnh nhân Đã Check-in** ở đầu trang và bật thử công tắc **Tự động làm mới 30s (Auto-Refresh)**.
3. Bấm **Khám bệnh** cho bệnh nhân **Bùi Tuấn Anh** (hoặc **Vũ Thị Hồng Nhung**):
   - Nhập **Mạch, Huyết áp, Nhiệt độ, SpO2, Cân nặng (kg), Chiều cao (cm)** -> Hệ thống tự động tính **Chỉ số BMI & phân loại thể trạng** theo thời gian thực và tổng hợp vào hồ sơ.
   - Tại cột **Lịch sử khám trước đây**, bấm nút **"Sao chép đơn thuốc này"** -> Toàn bộ danh sách thuốc và liều dùng của lần khám trước tự động nạp vào bảng kê đơn hiện tại.
   - Thử nhập số lượng thuốc lớn hơn tồn kho để xem **Cảnh báo vượt tồn kho trực tiếp (Live Over-Stock Warning)**.
   - Chọn nhanh mốc hẹn tái khám (*+7 ngày / +14 ngày / +30 ngày*).
   - Bấm **"In Phiếu Khám & Đơn Thuốc"** để xem mẫu phiếu in chuẩn Y tế Quốc gia (A4), sau đó bấm **"Hoàn thành ca khám"** (tự động trừ kho thuốc & gửi thông báo cho bệnh nhân).
