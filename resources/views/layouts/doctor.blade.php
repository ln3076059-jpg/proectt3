<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Doctor Dashboard') - Phòng khám Đa khoa Thái Thịnh</title>
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
            top: 75px;
            right: 24px;
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
        .btn-primary .text-muted,
        .btn-primary .text-secondary,
        .btn-primary .text-dark,
        .btn-primary .text-gray-800,
        .list-group-item.active,
        .list-group-item.active * {
            color: #ffffff !important;
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
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('doctor.dashboard') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="sidebar-brand-text mx-2" style="white-space: nowrap; font-weight: 700; letter-spacing: 0.3px;">125 Thái Thịnh <span class="badge badge-light text-primary font-weight-bold ml-1" style="font-size: 10px;">BS</span></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ (request()->routeIs('doctor.dashboard') && request('tab', 'overview') !== 'queue') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('doctor.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Bảng điều khiển Bác sĩ</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Khám bệnh & Tiếp nhận
            </div>

            <!-- Nav Item - Today's Appointments -->
            <li class="nav-item {{ request()->routeIs('doctor.appointments.*') || (request()->routeIs('doctor.dashboard') && request('tab') === 'queue') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('doctor.dashboard', ['tab' => 'queue']) }}">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Bệnh nhân tiếp đón hôm nay</span>
                </a>
            </li>

            <!-- Nav Item - Medical Records History -->
            <li class="nav-item {{ request()->routeIs('doctor.patient.history') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('doctor.patient.history') }}">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Lịch sử khám & EMR</span>
                </a>
            </li>

            <!-- Nav Item - Patient Records -->
            <li class="nav-item {{ request()->routeIs('doctor.patients.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('doctor.patients.index') }}">
                    <i class="fas fa-fw fa-user-injured"></i>
                    <span>Danh sách bệnh nhân</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Tài khoản & Hồ sơ
            </div>

            <!-- Nav Item - Doctor Profile -->
            <li class="nav-item {{ request()->routeIs('doctor.profile.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('doctor.profile.edit') }}">
                    <i class="fas fa-fw fa-id-card-alt"></i>
                    <span>Hồ sơ & Đổi mật khẩu</span>
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
                        <span class="text-xs font-weight-bold text-primary text-uppercase mb-1">Cổng Bác sĩ Khám chữa bệnh</span>
                        <h6 class="mb-0 font-weight-bold text-gray-800">
                            Phòng khám Đa khoa Thái Thịnh
                            @if(auth()->user() && auth()->user()->doctor && auth()->user()->doctor->department)
                                <span class="badge badge-info ml-2">{{ auth()->user()->doctor->department->name }}</span>
                            @endif
                        </h6>
                    </div>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="{{ route('doctor.dashboard') }}" title="Lịch hẹn khám hôm nay">
                                <span class="badge badge-primary px-2 py-1" style="font-size: 11.5px; font-weight: 600;">
                                    <i class="fas fa-calendar-check mr-1"></i>Ca trực hôm nay
                                </span>
                            </a>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @php
                                    $topDocUser = auth('web_doctor')->user() ?? auth()->user();
                                    $topDocName = $topDocUser->name ?? 'Bác sĩ';
                                    if (!\Illuminate\Support\Str::startsWith($topDocName, 'BS.')) {
                                        $topDocName = 'BS. ' . $topDocName;
                                    }
                                @endphp
                                <span class="mr-2 d-none d-lg-inline text-gray-700 font-weight-bold small">
                                    {{ $topDocName }}
                                </span>
                                @if(auth()->user() && auth()->user()->doctor && auth()->user()->doctor->avatar)
                                    <img class="img-profile rounded-circle" src="{{ asset('storage/' . auth()->user()->doctor->avatar) }}" style="object-fit: cover;">
                                @else
                                    <img class="img-profile rounded-circle" src="{{ asset('sb-admin-2/img/undraw_profile.svg') }}">
                                @endif
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('doctor.profile.edit') }}">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Hồ sơ bác sĩ
                                </a>
                                <a class="dropdown-item" href="{{ route('doctor.patient.history') }}">
                                    <i class="fas fa-history fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Lịch sử khám EMR
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
            <footer class="sticky-footer bg-white shadow-sm mt-4">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Bản quyền &copy; {{ date('Y') }} - Hệ thống Quản lý Phòng khám Đa khoa Thái Thịnh (125 Thái Thịnh, Đống Đa, Hà Nội)</span>
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
                <div class="modal-body">Bạn có chắc chắn muốn kết thúc phiên làm việc của Bác sĩ hiện tại không?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy bỏ</button>
                    <form action="{{ route('doctor.logout') }}" method="POST" class="d-inline">
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
            // Khởi tạo DataTables chuẩn tiếng Việt cho các bảng .dataTable hoặc #dataTable
            $('.dataTable, #dataTable').each(function () {
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
        });
    </script>
    @stack('scripts')
</body>
</html>
