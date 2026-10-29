# HỆ THỐNG QUẢN LÝ ĐẶT LỊCH KHÁM BỆNH – PHÒNG KHÁM THÁI THỊNH
> **ĐỒ ÁN TỐT NGHIỆP NGÀNH CÔNG NGHỆ THÔNG TIN**  
> **Cơ sở y tế:** Phòng khám Đa khoa Thái Thịnh (125 Thái Thịnh, Phường Thịnh Quang, Quận Đống Đa, TP. Hà Nội)  
> **Nền tảng công nghệ:** PHP 8.2 / Laravel 10.x / MySQL 8.0 InnoDB / Tailwind CSS / Blade / PHPUnit  

---

## 1. GIỚI THIỆU HỆ THỐNG
Hệ thống Quản lý đặt lịch khám bệnh cho **Phòng khám Thái Thịnh** là giải pháp phần mềm chuyên biệt phục vụ chuyển đổi số quy trình tiếp đón và phục vụ người bệnh. Hệ thống hỗ trợ người bệnh tra cứu chuyên khoa, chọn bác sĩ, đăng ký khung giờ khám theo ca chính xác, thanh toán viện phí trực tuyến qua VNPay và theo dõi hồ sơ khám bệnh điện tử.

Hệ thống được thiết kế theo tiêu chuẩn đồ án tốt nghiệp xuất sắc, chuẩn hóa kiến trúc MVC nhiều tầng, phân quyền đa vai trò (Admin, Doctor, Patient) và đặc biệt giải quyết triệt để bài toán **tranh chấp đồng thời (Concurrency & Race Condition)** khi nhiều người cùng đặt chỗ vào một khung giờ có số lượng giới hạn.

---

## 2. DANH MỤC CHỨC NĂNG HỆ THỐNG (F01 – F12)

### 2.1. Nhóm chức năng cốt lõi bắt buộc:
- **F01. Đăng ký tài khoản:** Đăng ký tài khoản bệnh nhân, mã hóa mật khẩu Bcrypt, tự động liên kết hồ sơ bệnh nhân trong giao dịch nhất quán.
- **F02. Đăng nhập, đăng xuất và phân quyền:** Hỗ trợ 3 vai trò (Administrator, Doctor, Patient) với các guards độc lập (`web_admin`, `web_doctor`, `web`), chống can thiệp URL (IDOR).
- **F03. Quản lý & Tra cứu chuyên khoa, bác sĩ:** Danh mục chuyên khoa, hồ sơ bác sĩ, học vị, kinh nghiệm, phòng làm việc và tra cứu khung giờ trống.
- **F04. Quản lý lịch làm việc & Khung giờ:** Thiết lập lịch làm việc theo ca (sáng/chiều) và tự động sinh các khung giờ khám chi tiết (ví dụ: 08:00 - 08:30, 08:30 - 09:00...) với hạn ngạch tiếp nhận (`capacity`).
- **F05. Đặt lịch khám & Chống trùng lịch (Trọng tâm kỹ thuật):** Quy trình đặt lịch đa bước, kiểm soát hạn ngạch bằng cơ chế khóa hàng độc quyền **MySQL InnoDB `lockForUpdate()`**, transaction ACID, cơ chế deadlock retry tự động.
- **F06. Lịch sử & Chi tiết lịch hẹn:** Xem danh sách lịch đã đặt, lọc theo trạng thái, kiểm soát quyền sở hữu qua Laravel Policy.
- **F07. Hủy lịch khám & Hoàn trả số chỗ:** Ràng buộc thời hạn hủy (trước tối thiểu 2 giờ), không cho phép hủy lịch đã hoàn thành, tự động hoàn trả chỗ trống (`booked_count--`) về khung giờ.
- **F08. Quản lý tài khoản người dùng:** Admin tra cứu, lọc theo vai trò, khóa hoặc mở khóa tài khoản.
- **F09. Quản lý chuyên khoa & bác sĩ:** Thêm, sửa thông tin, đổi trạng thái hoạt động; bảo toàn toàn vẹn dữ liệu tham chiếu (không xóa cứng khi đã có lịch sử).
- **F10. Quản lý lịch làm việc & slot trực quan:** Giao diện lịch quản trị cho phép xem số lượng đặt/chỗ còn, điều chỉnh capacity và khóa/mở slot tức thời.
- **F11. Quản lý lịch hẹn & Trạng thái:** Admin và Bác sĩ phê duyệt (`confirmed`), hoàn thành (`completed`), từ chối (`rejected`) và ghi vết toàn bộ vào bảng Audit History.
- **F12. Báo cáo thống kê Dashboard:** Dashboard quản trị với chỉ số thực tế từ Database: tổng lịch hẹn, doanh thu, biểu đồ phân bố trạng thái, tỷ lệ theo chuyên khoa và top bác sĩ.

### 2.2. Nhóm phân hệ mở rộng được bảo toàn:
- **Hồ sơ bệnh án điện tử (EMR):** Bác sĩ ghi nhận chẩn đoán, tiểu sử bệnh, hướng điều trị.
- **Kê đơn thuốc & Xét nghiệm:** Quản lý danh mục thuốc, kê đơn theo liều dùng, chỉ định xét nghiệm cận lâm sàng.
- **Cổng thanh toán VNPay Sandbox:** Xác thực chữ ký HMAC-SHA512 chống giả mạo số tiền và replay attack.
- **Tư vấn trực tuyến:** Chat nội bộ giữa bác sĩ và bệnh nhân.

---

## 3. YÊU CẦU MÔI TRƯỜNG VÀ CÔNG NGHỆ

- **Hệ điều hành:** Windows 10/11 hoặc Linux (Ubuntu 20.04+)
- **PHP:** Phiên bản 8.2 trở lên (Cần kích hoạt các extension: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`)
- **Web Server:** Apache (XAMPP) hoặc Nginx
- **Database:** MySQL 8.0+ hoặc MariaDB 10.4+ (Yêu cầu Storage Engine: **InnoDB**)
- **Composer:** Phiên bản 2.x
- **Node.js & NPM:** Node.js 18+ (Dùng để biên dịch assets)

---

## 4. HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY ỨNG DỤNG TRÊN WINDOWS

### Bước 1: Chuẩn bị thư mục mã nguồn
Mở terminal PowerShell tại thư mục dự án:
```powershell
cd "d:\.idea\it63thang9\thái"
```

### Bước 2: Cài đặt các gói phụ thuộc PHP
Nếu sử dụng môi trường PHP của XAMPP:
```powershell
& "D:\xampp1\php\php.exe" C:\ProgramData\ComposerSetup\bin\composer.phar install
```

### Bước 3: Cấu hình tệp môi trường `.env`
Tạo hoặc kiểm tra tệp `.env` đã thiết lập kết nối cơ sở dữ liệu MySQL:
```env
APP_NAME="Phòng khám Thái Thịnh"
APP_ENV=local
APP_KEY=base64:XG8d2Lq3yUqB+35uC/G7+4zT/T8B5F9Z/W9N6Q5K4L8=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phongkham_thaithinh
DB_USERNAME=root
DB_PASSWORD=
```

### Bước 4: Khởi tạo Cơ sở dữ liệu và Dữ liệu mẫu (Seed)
Chạy lệnh migrate để tạo toàn bộ 38 bảng cơ sở dữ liệu và nạp dữ liệu mẫu thực tế của Phòng khám Thái Thịnh:
```powershell
& "D:\xampp1\php\php.exe" artisan migrate:fresh --seed
```

### Bước 5: Khởi động máy chủ ứng dụng
```powershell
& "D:\xampp1\php\php.exe" artisan serve --port=8000
```
Truy cập hệ thống tại trình duyệt: **`http://localhost:8000`**

---

## 5. TÀI KHOẢN TRẢI NGHIỆM HỆ THỐNG (DEMO ACCOUNTS)

| Vai trò (Role) | Email đăng nhập | Mật khẩu mặc định | Ghi chú quyền hạn |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin@thaithinh.vn` | `admin123` | Toàn quyền quản trị, lịch làm việc, thống kê Dashboard F12 |
| **Bác sĩ (Doctor)** | `bs.minhanh@thaithinh.vn` | `doctor123` | Bác sĩ CKII Nguyễn Minh Anh - Trưởng khoa Nội |
| **Bác sĩ (Doctor)** | `bs.quanghuy@thaithinh.vn` | `doctor123` | Thạc sĩ BS Lê Quang Huy - Chuyên khoa Nhi |
| **Bác sĩ (Doctor)** | `bs.thuha@thaithinh.vn` | `doctor123` | Bác sĩ CKI Trần Thu Hà - Chuyên khoa Tim mạch |
| **Bệnh nhân (Patient)** | `benhnhan1@gmail.com` | `demo123` | Bệnh nhân Nguyễn Văn An (SĐT: 0912345671) |
| **Bệnh nhân (Patient)** | `benhnhan2@gmail.com` | `demo123` | Bệnh nhân Trần Thị Bích (SĐT: 0912345672) |

---

## 6. HƯỚNG DẪN CHẠY BỘ KIỂM THỬ TỰ ĐỘNG (TEST SUITE)

### 6.1. Chạy toàn bộ 36 bài kiểm thử chức năng & bảo mật (PHPUnit):
```powershell
& "D:\xampp1\php\php.exe" artisan test
```
*Kết quả:* **36/36 tests PASS 100%** (bao gồm luồng đặt lịch, bảo mật phân quyền 403, hủy lịch phục hồi capacity, xác thực người dùng).

### 6.2. Chạy bài kiểm thử xử lý đồng thời 20 workers song song (Concurrency Testing):
```powershell
& "D:\xampp1\php\php.exe" artisan test:concurrency
```
*Cơ chế:* Khởi chạy 20 tiến trình PHP CLI độc lập kết nối song song vào MySQL InnoDB để kiểm tra khả năng chịu tải và chống bán vượt (overselling) khi `capacity = 1` và `capacity = 5`.
*Kết quả:* **Đạt chuẩn 100%**, không xảy ra overselling, thời gian phản hồi p95 < 2.0 giây.

---

## 7. CẤU TRÚC THƯ MỤC VÀ TÀI LIỆU KỸ THUẬT

```
├── app/
│   ├── Http/Controllers/       # Các Controller phân hệ (Admin, Doctor, Client, Auth)
│   ├── Http/Middleware/        # RoleMiddleware phân quyền 3 vai trò
│   ├── Models/                 # 15+ Eloquent Models định nghĩa quan hệ
│   ├── Policies/               # AppointmentPolicy, MedicalRecordPolicy chống IDOR
│   └── Services/               # AppointmentService (Khóa hàng InnoDB & Transaction)
├── database/
│   ├── migrations/             # 38 migrations kiến trúc CSDL hoàn chỉnh
│   └── seeders/                # ThaiThinhClinicDemoSeeder tạo dữ liệu mẫu chuẩn
├── docs/                       # TÀI LIỆU KỸ THUẬT PHỤC VỤ BÁO CÁO ĐỒ ÁN:
│   ├── KIEN_TRUC_HE_THONG.md   # Kiến trúc đa tầng MVC, các vai trò và quy tắc nghiệp vụ
│   ├── THIET_KE_CO_SO_DU_LIEU.md # Sơ đồ quan hệ thực thể ERD & Từ điển dữ liệu chi tiết
│   ├── MA_TRAN_TRUY_VET_F01_F12.md # Ma trận truy vết yêu cầu phần mềm RTM
│   ├── BAO_CAO_KIEM_THU_DONG_THOI.md # Báo cáo chuyên sâu về Race Condition & Row-Level Lock
│   └── KE_HOACH_VA_KET_QUA_KIEM_THU.md # Kế hoạch và kết quả 38 ca kiểm thử chi tiết
├── resources/views/            # Giao diện Blade tiếng Việt đồng bộ
└── tests/                      # Bộ kiểm thử tự động Feature & Unit
```

---
*Đồ án tốt nghiệp đại học ngành Công nghệ thông tin - Phòng khám Đa khoa Thái Thịnh.*
