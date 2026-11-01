@extends('layouts.profile')

@section('title', 'Thông tin tài khoản - Phòng khám Thái Thịnh')
@section('breadcrumb_current', 'Thông tin tài khoản')

@section('content')
@php
    $activeProfileTab = request('tab', 'info');
@endphp
<div>
    {{-- Page Header & Sub-page Tabs --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                @if($activeProfileTab === 'password')
                    <i class="fas fa-shield-alt text-primary"></i> Đổi Mật Khẩu &amp; Bảo Mật Tài Khoản
                @else
                    <i class="far fa-user text-primary"></i> Thông Tin Hồ Sơ &amp; Thẻ BHYT
                @endif
            </h4>
            <small class="text-muted">
                {{ $activeProfileTab === 'password' ? 'Thiết lập mật khẩu mạnh và quản lý quyền riêng tư tài khoản bệnh nhân' : 'Quản lý hồ sơ hành chính, mã thẻ Bảo hiểm y tế (BHYT) và danh bạ người thân' }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('profile.edit', ['tab' => 'info']) }}"
               class="btn btn-sm {{ $activeProfileTab !== 'password' ? 'btn-tiki-primary' : 'btn-outline-secondary' }} rounded-pill px-3 fw-semibold">
                <i class="far fa-id-card me-1"></i> Hồ sơ &amp; BHYT
            </a>
            <a href="{{ route('profile.edit', ['tab' => 'password']) }}"
               class="btn btn-sm {{ $activeProfileTab === 'password' ? 'btn-tiki-primary' : 'btn-outline-secondary' }} rounded-pill px-3 fw-semibold">
                <i class="fas fa-key me-1"></i> Đổi mật khẩu
            </a>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if(session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> Thông tin tài khoản đã được cập nhật thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> Mật khẩu đã được thay đổi thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TAB 1: Health Overview & Profile Information --}}
    <div class="{{ $activeProfileTab === 'password' ? 'd-none' : '' }}">
    {{-- Health & Family Overview Card --}}
    <div class="tiki-card p-3 p-md-4 mb-4 border-primary-subtle" style="background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-white border shadow-sm">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="fas fa-notes-medical fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Lượt khám hoàn tất</div>
                        <div class="fw-bold text-dark fs-5">{{ $completedVisitsCount ?? 0 }} <span class="fs-6 fw-normal text-muted">lần khám</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-white border shadow-sm">
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="fas fa-calendar-check fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Lịch hẹn sắp tới</div>
                        <div class="fw-bold text-dark fs-5">{{ $upcomingVisitsCount ?? 0 }} <span class="fs-6 fw-normal text-muted">lịch hẹn</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between gap-2 p-2 rounded-3 bg-white border shadow-sm h-100">
                    <div>
                        <div class="text-muted small">Sổ khám bệnh điện tử</div>
                        <div class="fw-bold text-primary small">Tra cứu bệnh án &amp; đơn thuốc</div>
                    </div>
                    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-tiki-primary px-3">
                        <i class="fas fa-external-link-alt me-1"></i> Mở sổ
                    </a>
                </div>
            </div>
        </div>

        @if(isset($savedRelatives) && $savedRelatives->isNotEmpty())
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <span class="small fw-bold text-secondary">
                        <i class="fas fa-users text-primary me-1"></i> Danh bạ Người thân đã lưu trong hồ sơ đặt khám:
                    </span>
                    <a href="{{ route('appointments.create') }}" class="small text-decoration-none fw-semibold text-primary">
                        <i class="fas fa-plus-circle me-1"></i>Đặt lịch mới cho gia đình
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($savedRelatives as $rel)
                        <div class="badge bg-white text-dark border px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="font-weight: 500; font-size: 12.5px;">
                            <i class="fas fa-user-shield text-info"></i>
                            <strong>{{ $rel->relative_name }}</strong>
                            @if($rel->relative_relationship)
                                <span class="text-muted">({{ $rel->relative_relationship }})</span>
                            @endif
                            @if($rel->relative_dob)
                                <span class="text-secondary small">· NS: {{ \Carbon\Carbon::parse($rel->relative_dob)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Profile Information Card --}}
    <div class="tiki-card p-4 mb-4">
        <div class="pb-3 mb-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0">
                <i class="far fa-id-card text-primary me-2"></i>Thông Tin Cá Nhân
            </h6>
            <span class="badge bg-primary-subtle text-primary">Bệnh nhân</span>
        </div>
        @include('profile.partials.update-profile-information-form')
    </div>
    </div>

    {{-- TAB 2: Change Password & Account Security --}}
    <div class="{{ $activeProfileTab === 'password' ? '' : 'd-none' }}">
    {{-- Change Password Card --}}
    <div class="tiki-card p-4 mb-4">
        <div class="pb-3 mb-3 border-bottom">
            <h6 class="fw-bold text-dark mb-0">
                <i class="fas fa-lock text-primary me-2"></i>Đổi Mật Khẩu
            </h6>
        </div>
        @include('profile.partials.update-password-form')
    </div>

    {{-- Delete Account Card --}}
    <div class="tiki-card p-4 border-danger-subtle">
        <div class="pb-3 mb-3 border-bottom">
            <h6 class="fw-bold text-danger mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>Vùng Nguy Hiểm: Xóa Tài Khoản
            </h6>
        </div>
        @include('profile.partials.delete-user-form')
    </div>
    </div>
</div>
@endsection
