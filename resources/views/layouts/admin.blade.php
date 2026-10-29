<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - Phòng khám Đa khoa Thái Thịnh</title>
    <link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">

    <!-- Custom fonts for this template -->
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="{{ asset('sb-admin-2/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="{{ asset('sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
            background-color: #f8fafc;
        }
        /* Bootstrap 5 <-> Bootstrap 4 Compatibility Bridge */
        .fw-bold { font-weight: 700 !important; }
        .fw-semibold { font-weight: 600 !important; }
        .fs-5 { font-size: 1.15rem !important; }
        .fs-6 { font-size: 0.95rem !important; }
        .me-1 { margin-right: 0.25rem !important; }
        .me-2 { margin-right: 0.5rem !important; }
        .me-3 { margin-right: 1rem !important; }
        .ms-1 { margin-left: 0.25rem !important; }
        .ms-2 { margin-left: 0.5rem !important; }
        .ms-auto { margin-left: auto !important; }
        .text-end { text-align: right !important; }
        .text-start { text-align: left !important; }
        .gap-2 { gap: 0.5rem !important; }
        .gap-3 { gap: 1rem !important; }
        .global-toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1090;
            min-width: 300px;
            max-width: 440px;
            border-radius: 0.65rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.16);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .sidebar-brand-icon i {
            font-size: 1.5rem;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.65rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05) !important;
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            border-top-left-radius: 0.65rem !important;
            border-top-right-radius: 0.65rem !important;
        }
        .table thead th {
            vertical-align: middle;
            background-color: #f8fafc;
            color: #0284c7;
            font-weight: 700;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #f0f9ff;
        }
        .btn-circle.btn-sm {
            width: 2rem;
            height: 2rem;
            padding: 0;
            font-size: .8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease;
        }
        .btn-circle.btn-sm:hover {
            transform: translateY(-1px);
        }
        .card-header h6 {
            font-size: 0.98rem;
            letter-spacing: .01em;
        }
        .badge-status {
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.35em 0.65em;
        }
    </style>
    @stack('styles')
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-clinic-medical"></i>
                </div>
                <div class="sidebar-brand-text mx-2">Thái Thịnh <sup>ADMIN</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Nav Item - Statistics -->
            <li class="nav-item {{ request()->routeIs('admin.statistics.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.statistics.index') }}">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>Báo cáo & Thống kê</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Khám bệnh & Lịch hẹn
            </div>

            <li class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.appointments.index') }}">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Quản lý lịch hẹn</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Lịch làm việc & Khung giờ</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.doctors.index') }}">
                    <i class="fas fa-fw fa-user-md"></i>
                    <span>Đội ngũ bác sĩ</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.medical-records.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.medical-records.index') }}">
                    <i class="fas fa-fw fa-file-medical-alt"></i>
                    <span>Hồ sơ bệnh án</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.lab_tests.*') || request()->routeIs('admin.test-types.*') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('admin.lab_tests.*') || request()->routeIs('admin.test-types.*') ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseLab"
                    aria-expanded="true" aria-controls="collapseLab">
                    <i class="fas fa-fw fa-vial"></i>
                    <span>Quản lý xét nghiệm</span>
                </a>
                <div id="collapseLab" class="collapse {{ request()->routeIs('admin.lab_tests.*') || request()->routeIs('admin.test-types.*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Cận lâm sàng:</h6>
                        <a class="collapse-item {{ request()->routeIs('admin.lab_tests.*') ? 'active' : '' }}" href="{{ route('admin.lab_tests.index') }}">Kết quả xét nghiệm</a>
                        <a class="collapse-item {{ request()->routeIs('admin.test-types.*') ? 'active' : '' }}" href="{{ route('admin.test-types.index') }}">Loại xét nghiệm</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Danh mục & Quản trị
            </div>

            <li class="nav-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.departments.index') }}">
                    <i class="fas fa-fw fa-hospital-alt"></i>
                    <span>Khoa phòng</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.services.index') }}">
                    <i class="fas fa-fw fa-stethoscope"></i>
                    <span>Dịch vụ khám</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.medicines.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.medicines.index') }}">
                    <i class="fas fa-fw fa-pills"></i>
                    <span>Danh mục thuốc</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.diseases.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.diseases.index') }}">
                    <i class="fas fa-fw fa-virus"></i>
                    <span>Danh mục bệnh</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.patients.index') }}">
                    <i class="fas fa-fw fa-user-injured"></i>
                    <span>Hồ sơ bệnh nhân</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-fw fa-users-cog"></i>
                    <span>Tài khoản người dùng</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.chat.index') }}">
                    <i class="fas fa-fw fa-comments"></i>
                    <span>Tư vấn trực tuyến</span>
                    <span id="chat-notification-badge" class="badge badge-danger badge-counter d-none"></span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Context -->
                    <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100">
                        <span class="text-xs font-weight-bold text-primary text-uppercase mb-1">Hệ thống Quản lý Phòng khám</span>
                        <h6 class="mb-0 font-weight-bold text-gray-800">125 Thái Thịnh, Đống Đa, Hà Nội</h6>
                    </div>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="{{ route('admin.appointments.index') }}" title="Lịch hẹn">
                                <i class="fas fa-bell fa-fw"></i>
                                @if(isset($newAppointmentsCount) && $newAppointmentsCount > 0)
                                    <span class="badge badge-danger badge-counter">{{ $newAppointmentsCount }}</span>
                                @endif
                            </a>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="{{ route('admin.chat.index') }}" title="Tin nhắn tư vấn">
                                <i class="fas fa-envelope fa-fw"></i>
                                <span id="topbar-chat-badge" class="badge badge-success badge-counter d-none"></span>
                            </a>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name ?? 'Quản trị viên' }}</span>
                                <img class="img-profile rounded-circle"
                                    src="{{ asset('sb-admin-2/img/undraw_profile.svg') }}">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Dashboard
                                </a>
                                <a class="dropdown-item" href="{{ route('home') }}" target="_blank">
                                    <i class="fas fa-external-link-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Xem Trang chủ người bệnh
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Đăng xuất
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Bản quyền &copy; Phòng khám Đa khoa Thái Thịnh {{ date('Y') }} - Hệ thống Quản lý Đặt lịch Khám bệnh</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-gray-800" id="logoutModalLabel">Xác nhận đăng xuất</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Đóng">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Bạn có chắc chắn muốn kết thúc phiên làm việc của Quản trị viên hiện tại không?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy bỏ</button>
                    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Đăng xuất ngay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Toast Notification for Instant Visual Feedback --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
        @php
            $toastType = session('error') ? 'danger' : (session('warning') ? 'warning' : (session('info') ? 'info' : 'success'));
            $toastMsg = session('error') ?? session('warning') ?? session('info') ?? session('success');
            $toastIcon = session('error') ? 'fa-exclamation-circle' : (session('warning') ? 'fa-exclamation-triangle' : 'fa-check-circle');
        @endphp
        <div id="globalToastContainer" class="alert alert-{{ $toastType }} global-toast-notification d-flex align-items-center justify-content-between py-3 px-3 mb-0" role="alert">
            <div class="d-flex align-items-center pr-2">
                <i class="fas {{ $toastIcon }} fa-lg mr-2"></i>
                <span class="font-weight-bold small">{{ $toastMsg }}</span>
            </div>
            <button type="button" class="close ml-2" onclick="document.getElementById('globalToastContainer').remove();" aria-label="Đóng">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('sb-admin-2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('sb-admin-2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('sb-admin-2/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('sb-admin-2/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('sb-admin-2/vendor/chart.js/Chart.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            // Khởi tạo DataTables chuẩn tiếng Việt cho các bảng .dataTable (bỏ qua bảng đã phân trang server-side .no-datatable)
            $('.dataTable:not(.no-datatable), #dataTable:not(.no-datatable)').each(function () {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        "language": {
                            "search": "Tìm kiếm nhanh:",
                            "lengthMenu": "Hiển thị _MENU_ dòng",
                            "info": "Đang xem _START_ đến _END_ trong _TOTAL_ dòng",
                            "infoEmpty": "Chưa có dữ liệu",
                            "infoFiltered": "(lọc từ _MAX_ mục)",
                            "paginate": {
                                "first": "Đầu",
                                "last": "Cuối",
                                "next": "Sau",
                                "previous": "Trước"
                            },
                            "emptyTable": "Không có dữ liệu trong bảng"
                        },
                        "pageLength": 10
                    });
                }
            });

            // Real-time Chat Unread Polling (Tối ưu: Tạm dừng khi ẩn tab trình duyệt để tiết kiệm tài nguyên)
            function updateChatBadge() {
                if (document.hidden) return;
                $.ajax({
                    url: "{{ route('admin.chat.unread_count') }}",
                    method: 'GET',
                    success: function (data) {
                        var count = (data && typeof data.count !== 'undefined') ? Number(data.count) : 0;
                        if (count > 0) {
                            $('#chat-notification-badge').text(count).removeClass('d-none');
                            $('#topbar-chat-badge').text(count).removeClass('d-none');
                        } else {
                            $('#chat-notification-badge').text('').addClass('d-none');
                            $('#topbar-chat-badge').text('').addClass('d-none');
                        }
                    }
                });
            }
            updateChatBadge();
            setInterval(updateChatBadge, 4000);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) updateChatBadge();
            });

            // Tự động ẩn Toast Notification sau 5 giây
            var toastEl = document.getElementById('globalToastContainer');
            if (toastEl) {
                setTimeout(function () {
                    if (toastEl && toastEl.parentNode) {
                        toastEl.style.opacity = '0';
                        toastEl.style.transform = 'translateY(-10px)';
                        setTimeout(function () { if (toastEl.parentNode) toastEl.remove(); }, 300);
                    }
                }, 5000);
            }

            // Chống bấm đúp (Double-Submit Prevention) & Hiển thị Loading Spinner trên các form gửi dữ liệu
            $('form').on('submit', function () {
                var method = ($(this).attr('method') || 'GET').toUpperCase();
                if (method === 'GET') return true;
                var $btn = $(this).find('button[type="submit"]:focus, button[type="submit"]:first').first();
                if ($btn.length && !$btn.hasClass('js-submitting')) {
                    $btn.addClass('js-submitting');
                    setTimeout(function () { $btn.prop('disabled', true); }, 10);
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
