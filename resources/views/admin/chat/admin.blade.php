@extends('layouts.admin')

@section('title', 'Tư vấn trực tuyến với Bệnh nhân')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-comments text-primary mr-2"></i>Tư vấn trực tuyến với Bệnh nhân
    </h1>
</div>

{{-- Chat CSS --}}
<link rel="stylesheet" href="{{ asset('css/chat/chat_admin.css') }}">

<!-- Chat Card (SB Admin 2) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-headset mr-1"></i> Trung tâm hỗ trợ & Tư vấn người bệnh
        </h6>
    </div>
    <div class="card-body p-0">
        <div id="admin-chat-box" class="chat-box" style="border: none; box-shadow: none;">
            <div class="row no-gutters">
                <!-- Left Side: User List -->
                <div class="col-md-4 border-right bg-light chat-users" style="max-height: 520px; overflow-y: auto;">
                    <div class="p-3 bg-white border-bottom font-weight-bold text-primary small text-uppercase">
                        <i class="fas fa-user-friends mr-1"></i> Danh sách bệnh nhân
                    </div>
                    <div class="p-2 bg-white border-bottom">
                        <input type="text" id="filterChatUsersInput" class="form-control form-control-sm" placeholder="Tìm tên hoặc SĐT bệnh nhân...">
                    </div>
                    <ul class="list-group list-group-flush" id="user-list">
                        @foreach($users as $user)
                            @php
                                $latestAppt = optional(optional($user->patient)->appointments)->first();
                                $apptSummary = $latestAppt
                                    ? ('#' . ($latestAppt->appointment_code ?: $latestAppt->id) . ' • ' . \Carbon\Carbon::parse($latestAppt->appointment_date)->format('d/m/Y') . ' • BS. ' . (optional(optional($latestAppt->doctor)->user)->name ?? 'Chuyên khoa') . ' (' . $latestAppt->status . ')')
                                    : 'Chưa có lịch hẹn khám';
                                $phoneStr = optional($user->patient)->phone ?: ($user->phone ?: 'Chưa cập nhật SĐT');
                            @endphp
                            <li class="list-group-item user-item py-3"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-phone="{{ $phoneStr }}"
                                data-email="{{ $user->email }}"
                                data-appt="{{ $apptSummary }}"
                                data-appt-url="{{ $latestAppt ? route('admin.appointments.index', ['search' => $latestAppt->appointment_code ?: $user->name]) : route('admin.appointments.index', ['search' => $user->name]) }}"
                                style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <div class="mr-2">
                                        <i class="fas fa-user-circle fa-2x text-gray-400"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold text-gray-800">{{ $user->name }}</div>
                                        <small class="text-muted d-block">{{ $phoneStr }} • {{ $user->email }}</small>
                                    </div>
                                    <span class="badge badge-danger badge-counter d-none unread-badge" data-user-id="{{ $user->id }}"></span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Right Side: Chat Content -->
                <div class="col-md-8 d-flex flex-column chat-content" style="min-height: 520px;">
                    {{-- Thanh Ngữ cảnh Hồ sơ & Lịch hẹn của Bệnh nhân đang chat --}}
                    <div id="adminChatPatientContext" class="d-none p-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                        <div class="small">
                            <strong class="text-primary" id="ctxPatientName">-</strong>
                            <span class="text-muted mx-1">|</span>
                            <span>SĐT: <strong id="ctxPatientPhone">-</strong></span>
                            <div class="text-xs text-gray-700 mt-1">
                                <i class="far fa-calendar-check text-info mr-1"></i>Lịch gần nhất: <strong id="ctxPatientAppt">-</strong>
                            </div>
                        </div>
                        <a href="#" id="ctxPatientApptUrl" class="btn btn-xs btn-outline-primary py-1 px-2 font-weight-bold" style="font-size: 11.5px;">
                            <i class="fas fa-external-link-alt mr-1"></i>Tra cứu lịch hẹn
                        </a>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chat-messages" class="chat-messages bg-white flex-grow-1 p-3" style="overflow-y: auto; max-height: 390px;">
                        <div class="text-muted text-center py-5">
                            <i class="far fa-comments fa-3x text-gray-300 mb-3 d-block"></i>
                            Vui lòng chọn một bệnh nhân từ danh sách bên trái để bắt đầu tư vấn.
                        </div>
                    </div>

                    {{-- Gợi ý câu trả lời nhanh chuẩn CSKH Y tế --}}
                    <div id="adminQuickRepliesBar" class="d-none px-3 py-2 bg-white border-top">
                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="text-xs text-muted font-weight-bold mr-1"><i class="fas fa-bolt text-warning mr-1"></i>Mẫu phản hồi nhanh:</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 js-cskh-reply" style="font-size: 11.5px; border-radius: 12px;" data-text="Chào anh/chị, Phòng khám Đa khoa 125 Thái Thịnh đã xác nhận lịch hẹn của mình. Anh/chị vui lòng đến trước giờ hẹn 10 phút và đưa mã QR tại quầy ưu tiên tầng 1 nhé ạ!">
                                + Xác nhận lịch &amp; Hướng dẫn QR
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 js-cskh-reply" style="font-size: 11.5px; border-radius: 12px;" data-text="Dạ Phòng khám 125 Thái Thịnh làm việc từ 07:30 đến 20:00 tất cả các ngày trong tuần và có áp dụng thanh toán Bảo hiểm Y tế. Anh/chị nhớ mang theo CCCD/VNeID khi đến khám ạ.">
                                + Giờ làm việc &amp; Thủ tục BHYT
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 js-cskh-reply" style="font-size: 11.5px; border-radius: 12px;" data-text="Dạ bộ phận Kế toán của Phòng khám đã tiếp nhận yêu cầu hoàn tiền VNPay của anh/chị và đang xử lý hoàn khoản trong 1-3 ngày làm việc ạ.">
                                + Thông báo hoàn tiền VNPay
                            </button>
                        </div>
                    </div>

                    <!-- Chat Input Form -->
                    <form id="chat-form" class="chat-input d-none p-3 border-top bg-light">
                        @csrf
                        <input type="hidden" id="receiver_id">
                        <div class="input-group">
                            <input type="text" id="message" class="form-control" placeholder="Nhập câu trả lời tư vấn cho bệnh nhân..." autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane mr-1"></i> Gửi tin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.ChatAdminConfig = {
        authId: {{ $authId }},
        csrfToken: "{{ csrf_token() }}",
        messagesUrlTemplate: "{{ route('admin.chat.messages', ['receiverId' => '__RID__']) }}",
        unreadByUserUrl: "{{ route('admin.chat.unread_by_user') }}",
        sendUrl: "{{ route('admin.chat.send') }}"
    };

    document.addEventListener('DOMContentLoaded', function () {
        const filterInput = document.getElementById('filterChatUsersInput');
        if (filterInput) {
            filterInput.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('#user-list .user-item').forEach(li => {
                    const txt = li.textContent.toLowerCase();
                    li.style.display = (!q || txt.includes(q)) ? '' : 'none';
                });
            });
        }

        const ctxBar = document.getElementById('adminChatPatientContext');
        const quickBar = document.getElementById('adminQuickRepliesBar');
        document.querySelectorAll('#user-list .user-item').forEach(li => {
            li.addEventListener('click', function () {
                if (ctxBar) {
                    ctxBar.classList.remove('d-none');
                    document.getElementById('ctxPatientName').textContent = this.getAttribute('data-name') || '-';
                    document.getElementById('ctxPatientPhone').textContent = this.getAttribute('data-phone') || '-';
                    document.getElementById('ctxPatientAppt').textContent = this.getAttribute('data-appt') || '-';
                    document.getElementById('ctxPatientApptUrl').href = this.getAttribute('data-appt-url') || '#';
                }
                if (quickBar) {
                    quickBar.classList.remove('d-none');
                }
            });
        });

        document.querySelectorAll('.js-cskh-reply').forEach(btn => {
            btn.addEventListener('click', function () {
                const msgInput = document.getElementById('message');
                if (msgInput) {
                    msgInput.value = this.getAttribute('data-text') || '';
                    msgInput.focus();
                }
            });
        });
    });
</script>
<script src="{{ asset('js/chat/chat_admin.js') }}"></script>
@endpush
