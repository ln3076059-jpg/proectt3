<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - Phòng khám Đa khoa Thái Thịnh</title>
    <link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">

    <!-- Custom fonts for this template (SB Admin 2 Nunito) -->
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&subset=vietnamese" rel="stylesheet">

    <!-- Custom styles for this template (SB Admin 2) -->
    <link href="{{ asset('sb-admin-2/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this page (DataTables Bootstrap 4) -->
    <link href="{{ asset('sb-admin-2/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: "Nunito", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #5a5c69;
            background-color: #f8f9fc;
            font-size: 0.92rem;
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
        .gap-1 { gap: 0.35rem !important; }
        .gap-2 { gap: 0.6rem !important; }
        .gap-3 { gap: 1rem !important; }
        .gap-4 { gap: 1.5rem !important; }

        /* Authentic SB Admin 2 Card & Table Format (tables.html) */
        .card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            border-top-left-radius: 0.35rem !important;
            border-top-right-radius: 0.35rem !important;
        }
        .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: #4e73df;
        }
        /* Ensure tables inside cards always have comfortable SB Admin 2 tables.html padding & fit page */
        .card > .card-body.p-0 {
            padding: 1.25rem !important;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100% !important;
            margin-bottom: 0;
            color: #5a5c69;
        }
        .table-bordered {
            border: 1px solid #e3e6f0;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #e3e6f0;
        }
        .table thead th,
        .table tfoot th {
            vertical-align: middle !important;
            background-color: #f8f9fc;
            color: #4e73df;
            font-weight: 800;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border-bottom-width: 2px;
            padding: 0.58rem 0.6rem;
        }
        .table td {
            vertical-align: middle !important;
            padding: 0.52rem 0.6rem;
            font-size: 0.86rem;
            line-height: 1.4;
        }
        .table td:last-child:not([colspan]) {
            white-space: nowrap;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.04);
        }

        /* Authentic SB Admin 2 Form Controls, Labels & Input Groups */
        .form-group label,
        form label {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #4e73df;
            margin-bottom: 0.4rem;
        }
        .form-group .custom-control-label {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: none;
            color: #5a5c69;
        }
        .form-control {
            font-size: 0.9rem;
            color: #5a5c69;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            height: calc(1.5em + 0.85rem + 2px);
            padding: 0.425rem 0.75rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        textarea.form-control {
            height: auto;
        }
        .form-control:focus {
            color: #5a5c69;
            background-color: #fff;
            border-color: #bac8f3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fc;
            border-color: #d1d3e2;
            color: #4e73df;
            font-weight: 700;
        }

        /* Button Spacing & SB Admin 2 Circle / Split Buttons */
        .table td .btn:not(.btn-block) {
            margin: 3px 4px !important;
            vertical-align: middle;
        }
        .table td form.d-inline,
        .table td form.d-inline-block {
            display: inline-block !important;
            margin: 0 !important;
            vertical-align: middle;
        }
        .btn-group:not(.btn-group-toggle) {
            display: inline-flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            box-shadow: none !important;
        }
        .btn-group:not(.btn-group-toggle) > .btn {
            flex: 0 0 auto !important;
            border-radius: 0.35rem !important;
            margin: 0 !important;
        }
        #content .container-fluid > .d-sm-flex.align-items-center.justify-content-between {
            flex-wrap: wrap !important;
            gap: 12px 16px !important;
        }
        #content .container-fluid > .d-sm-flex.align-items-center.justify-content-between > div:nth-child(2) {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 8px !important;
        }
        #content .container-fluid > .d-sm-flex.align-items-center.justify-content-between > div:nth-child(2) > .btn,
        #content .container-fluid > .d-sm-flex.align-items-center.justify-content-between > div:nth-child(2) > form {
            margin: 0 !important;
        }
        .card-header.d-flex {
            gap: 10px 16px;
        }
        .card-header > div > .btn {
            margin: 3px 4px !important;
        }
        :not(.btn-group):not(.input-group):not(.input-group-append):not(.input-group-prepend) > .btn + .btn,
        :not(.btn-group) > .btn + form.d-inline,
        :not(.btn-group) > form.d-inline + .btn,
        :not(.btn-group) > form.d-inline + form.d-inline {
            margin-left: 6px;
        }
        .d-flex > .btn,
        .d-inline-flex > .btn,
        .modal-footer > .btn {
            margin-top: 3px;
            margin-bottom: 3px;
        }
        .btn-circle.btn-sm {
            width: 2rem;
            height: 2rem;
            padding: 0;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-circle.btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        /* Contextual Visual Cards: True Chair-Shaped Card (.sb-slot-card) */
        .sb-slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(172px, 1fr));
            gap: 22px 16px;
            padding: 12px 4px 10px 4px;
        }
        .sb-slot-card {
            position: relative;
            border-top: 3px solid #1cc88a;
            border-bottom: 5px solid #1cc88a;
            border-left: 7px solid #1cc88a;
            border-right: 7px solid #1cc88a;
            border-radius: 14px 14px 10px 10px;
            background: #ffffff;
            padding: 10px 10px 8px 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(58, 59, 69, 0.08);
        }
        /* Lưng tựa ghế bo tròn phía trên (Chair Backrest) */
        .sb-slot-card::before {
            content: "";
            position: absolute;
            top: -13px;
            left: 10px;
            right: 10px;
            height: 11px;
            border-radius: 12px 12px 0 0;
            background: #1cc88a;
            box-shadow: inset 0 2px 0 rgba(255,255,255,0.35);
        }
        /* Hai chân ghế phía dưới (Chair Legs) */
        .sb-slot-card::after {
            content: "";
            position: absolute;
            bottom: -11px;
            left: 14px;
            right: 14px;
            height: 7px;
            background: linear-gradient(90deg, #5a5c69 0 12px, transparent 12px calc(100% - 12px), #5a5c69 calc(100% - 12px) 100%);
            border-radius: 0 0 3px 3px;
        }
        .sb-slot-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 16px rgba(58, 59, 69, 0.15);
        }
        .sb-slot-card.is-available {
            border-color: #1cc88a;
            background: linear-gradient(180deg, #f2fff9 0%, #ffffff 100%);
        }
        .sb-slot-card.is-available::before {
            background: linear-gradient(90deg, #17a673, #1cc88a, #17a673);
        }
        .sb-slot-card.is-busy {
            border-color: #4e73df;
            background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 100%);
        }
        .sb-slot-card.is-busy::before {
            background: linear-gradient(90deg, #2e59d9, #4e73df, #2e59d9);
        }
        .sb-slot-card.is-full {
            border-color: #f6c23e;
            background: linear-gradient(180deg, #fffcf0 0%, #ffffff 100%);
        }
        .sb-slot-card.is-full::before {
            background: linear-gradient(90deg, #dda20a, #f6c23e, #dda20a);
        }
        .sb-slot-card.is-locked {
            border-color: #e74a3b;
            background: #f8f9fc;
            opacity: 0.88;
        }
        .sb-slot-card.is-locked::before {
            background: linear-gradient(90deg, #be2617, #e74a3b, #be2617);
        }
        .sb-slot-seat-icon {
            width: 30px;
            height: 30px;
            border-radius: 0.4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
        }
        /* Dãy ghế con bên trong đệm ghế (Mini-seats inside slot cushion) */
        .sb-mini-seats {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 5px 6px;
            margin: 6px 0;
            background: rgba(248, 249, 252, 0.9);
            border: 1px dashed #d1d3e2;
            border-radius: 6px;
            justify-content: center;
        }
        .sb-mini-seat {
            width: 22px;
            height: 22px;
            border-radius: 4px 4px 2px 2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            border: 1px solid transparent;
        }
        .sb-mini-seat.seat-free {
            background: #e6f9f0;
            color: #1cc88a;
            border-color: #b7ebd3;
        }
        .sb-mini-seat.seat-taken {
            background: #4e73df;
            color: #ffffff;
            border-color: #2e59d9;
        }
        .sb-mini-seat.seat-locked {
            background: #fce8e6;
            color: #e74a3b;
            border-color: #f5c2c0;
        }

        /* Contextual Domain Cards across Admin Modules */
        .card .text-xs.font-weight-bold.text-uppercase {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #content-wrapper .d-flex.justify-content-between {
            gap: 6px 10px;
        }
        #content-wrapper .row.no-gutters.align-items-center > .col:not(.mr-2) {
            margin-right: 0.5rem;
            min-width: 0;
        }
        .sb-doctor-badge-card,
        .sb-ticket-card,
        .sb-bhyt-card,
        .sb-blister-card,
        .sb-folder-card,
        .sb-lab-slip-card,
        .sb-room-card,
        .sb-slot-card {
            min-width: 0;
        }
        .sb-ticket-card .badge,
        .sb-bhyt-card .badge {
            max-width: 100%;
            white-space: normal;
            text-align: left;
            line-height: 1.3;
        }
        /* 1. Thẻ đeo Bác sĩ (Doctor ID Badge Card) */
        .sb-doctor-badge-card {
            position: relative;
            border: 1px solid #d1d3e2;
            border-top: 5px solid #4e73df;
            border-radius: 0.5rem;
            background: #ffffff;
            padding: 14px 14px 12px 14px;
            box-shadow: 0 2px 8px rgba(58, 59, 69, 0.07);
            margin-top: 8px;
        }
        .sb-doctor-badge-card::before {
            content: "";
            position: absolute;
            top: -10px;
            left: calc(50% - 16px);
            width: 32px;
            height: 8px;
            border-radius: 4px;
            background: #858796;
            border: 2px solid #fff;
        }
        /* 2. Vé thứ tự tiếp đón đục lỗ (Perforated Queue Ticket Card) */
        .sb-ticket-card {
            position: relative;
            border: 1px solid #bac8f3;
            border-left: 5px solid #36b9cc;
            border-radius: 0.45rem;
            background: linear-gradient(90deg, #f8fdff 0%, #ffffff 100%);
            padding: 12px 16px;
            box-shadow: 0 2px 6px rgba(58, 59, 69, 0.06);
        }
        .sb-ticket-card::after {
            content: "";
            position: absolute;
            top: calc(50% - 8px);
            right: -8px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #f8f9fc;
            border-left: 1px solid #bac8f3;
        }
        /* 3. Thẻ Bảo hiểm Y tế Bệnh nhân (BHYT Smart Card) */
        .sb-bhyt-card {
            border: 1px solid #b7ebd3;
            border-radius: 0.55rem;
            background: linear-gradient(135deg, #f4fff9 0%, #ffffff 65%, #eef6ff 100%);
            padding: 12px 14px;
            box-shadow: 0 2px 8px rgba(28, 200, 138, 0.08);
            position: relative;
            overflow: hidden;
        }
        .sb-bhyt-chip {
            width: 28px;
            height: 20px;
            border-radius: 4px;
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            border: 1px solid #b8860b;
            display: inline-block;
        }
        /* 4. Vỉ thuốc / Hộp Dược phẩm (Medicine Blister Pack Card) */
        .sb-blister-card {
            border: 1px solid #d1d3e2;
            border-radius: 0.5rem;
            background: radial-gradient(#e3e6f0 1.5px, transparent 1.5px), #ffffff;
            background-size: 12px 12px;
            padding: 12px 14px;
            box-shadow: inset 0 0 0 4px #f8f9fc, 0 2px 6px rgba(58, 59, 69, 0.06);
        }
        /* 5. Bìa Hồ sơ Bệnh án Điện tử (EMR Folder Card) */
        .sb-folder-card {
            position: relative;
            border: 1px solid #d1d3e2;
            border-top: 3px solid #4e73df;
            border-radius: 0 0.45rem 0.45rem 0.45rem;
            background: #fffdf9;
            padding: 12px 14px;
            margin-top: 10px;
            box-shadow: 0 2px 6px rgba(58, 59, 69, 0.06);
        }
        .sb-folder-card::before {
            content: "HỒ SƠ EMR";
            position: absolute;
            top: -17px;
            left: -1px;
            background: #4e73df;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 5px 5px 0 0;
            letter-spacing: 0.04em;
        }
        .sb-folder-card[data-tab-label]::before {
            content: attr(data-tab-label);
        }
        /* 6. Ống nghiệm & Phiếu Cận lâm sàng (Lab Specimen Tube Card) */
        .sb-lab-slip-card {
            border: 1px solid #d1d3e2;
            border-top: 6px solid #36b9cc;
            border-radius: 0.35rem 0.35rem 0.75rem 0.75rem;
            background: #ffffff;
            padding: 12px 14px;
            box-shadow: 0 2px 7px rgba(54, 185, 204, 0.08);
        }

        /* Contextual Clinic Room / Department Architecture Card (.sb-room-card) */
        .sb-room-card {
            border: 1px solid #e3e6f0;
            border-top: 4px solid #4e73df;
            border-radius: 0.4rem;
            background: #fff;
            padding: 12px 15px;
            box-shadow: 0 2px 8px rgba(58, 59, 69, 0.06);
        }
        .sb-room-card:has(> .sb-room-icon) {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sb-room-icon {
            width: 42px;
            height: 42px;
            border-radius: 0.4rem;
            background: #eef2ff;
            color: #4e73df;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .global-toast-notification {
            position: fixed;
            top: 75px;
            right: 24px;
            z-index: 1090;
            min-width: 300px;
            max-width: 440px;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .sidebar-brand-icon i {
            font-size: 1.5rem;
        }
        .badge-status {
            font-size: 0.8rem;
            font-weight: 700;
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
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-clinic-medical"></i>
                </div>
                <div class="sidebar-brand-text mx-2" style="white-space: nowrap; font-weight: 700; letter-spacing: 0.3px;">125 Thái Thịnh <span class="badge badge-light text-primary font-weight-bold ml-1" style="font-size: 10px;">Admin</span></div>
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
            // Đồng bộ tfoot theo chuẩn SB Admin 2 tables.html cho mọi bảng dữ liệu .table-bordered
            $('.table-bordered').each(function () {
                var $tbl = $(this);
                if ($tbl.find('thead').length && !$tbl.find('tfoot').length && $tbl.find('tbody tr td:not([colspan])').length > 0) {
                    var $tfoot = $('<tfoot></tfoot>').html($tbl.find('thead').html());
                    $tbl.find('thead').after($tfoot);
                }
            });

            // Khởi tạo DataTables chuẩn tiếng Việt cho các bảng .dataTable (bỏ qua bảng đã phân trang server-side .no-datatable hoặc bảng ẩn .d-none)
            $('.dataTable:not(.no-datatable), #dataTable:not(.no-datatable)').each(function () {
                if ($(this).closest('.d-none').length) return;
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
                        "pageLength": 5
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
