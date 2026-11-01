{{-- Chat Widget Container --}}
<div id="chat-widget" data-ui>
    <div id="chat-toggle" class="chat-bubble position-relative" title="Hỗ trợ trực tuyến 125 Thái Thịnh">
        <i class="fa-solid fa-comment-dots"></i>
        <span id="chat-notify" class="notify-badge d-none"></span>
    </div>

    <div id="chat-box" class="chat-box hidden">
        <div class="chat-header d-flex justify-content-between align-items-center text-white">
            <div>
                <div class="fw-semibold text-white" style="font-size: var(--step-body);">Tổng đài 125 Thái Thịnh</div>
                <div class="ts-small text-white" style="color: rgba(255, 255, 255, 0.92) !important;">Trực tuyến · Phản hồi trực tiếp</div>
            </div>
            <button id="chat-close" class="btn btn-sm text-white p-0 border-0" style="font-size: 22px; line-height: 1;">&times;</button>
        </div>

        <div class="p-2 d-flex flex-wrap gap-2" style="background-color: var(--surface);">
            <button type="button" class="ts-spec-btn" onclick="fillChatPrompt('Tôi cần tư vấn đặt lịch khám chuyên khoa')">
                Tư vấn đặt khám
            </button>
            <button type="button" class="ts-spec-btn" onclick="fillChatPrompt('Cho tôi hỏi bảng giá gói khám tổng quát?')">
                Hỏi bảng giá
            </button>
            <button type="button" class="ts-spec-btn" onclick="fillChatPrompt('Phòng khám có áp dụng thẻ BHYT không?')">
                Thủ tục BHYT
            </button>
            <a href="{{ route('advisor.index') }}" class="ts-spec-btn text-decoration-none" style="color: var(--accent);">
                <i class="bi bi-clipboard2-pulse me-1"></i>Tra cứu triệu chứng
            </a>
        </div>

        <div id="chat-messages" class="p-3" style="height: 280px; overflow-y: auto; background-color: var(--bg);">
            @auth
                <p class="text-center ts-small" style="color: var(--muted);">Đang kết nối điều dưỡng tư vấn...</p>
            @else
                <div class="p-3 rounded border bg-white text-center">
                    <div class="fw-semibold mb-1" style="font-size: 13px;">Chào mừng bạn đến với PKĐK 125 Thái Thịnh</div>
                    <p class="ts-small mb-2" style="color: var(--muted);">
                        Vui lòng đăng nhập để trò chuyện trực tiếp với Điều dưỡng CSKH hoặc sử dụng công cụ Tư vấn triệu chứng miễn phí.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('patient.login') }}" class="ts-btn ts-btn-sm text-decoration-none">Đăng nhập Chat</a>
                        <a href="{{ route('advisor.index') }}" class="ts-btn-secondary ts-btn-sm text-decoration-none">Tư vấn triệu chứng</a>
                    </div>
                </div>
            @endauth
        </div>

        <form id="chat-form" class="d-flex gap-2 p-2" style="background-color: var(--surface);" data-user-id="{{ Auth::id() ?? '' }}">
            @csrf
            <input type="hidden" id="receiver_id" value="{{ $receiverId ?? '' }}">
            <input type="text"
                   id="message"
                   class="form-control px-3"
                   placeholder="Nhập câu hỏi của bạn...">
            <button class="ts-btn px-3" type="submit">
                Gửi
            </button>
        </form>
    </div>
</div>

<style>
    #chat-widget {
        position: fixed;
        bottom: 20px;
        right: 12px;
        z-index: 1040;
        font-family: var(--default-font, "Roboto", sans-serif);
    }

    .chat-bubble {
        background-color: var(--accent-color, #1977cc);
        color: #ffffff;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        box-shadow: 0 4px 14px rgba(25, 119, 204, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .chat-bubble:hover {
        background-color: #166ab5;
        transform: translateY(-2px);
    }

    .chat-box {
        width: 360px;
        max-width: calc(100vw - 30px);
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(44, 73, 100, 0.18);
        border: 1px solid rgba(44, 73, 100, 0.12);
        overflow: hidden;
        position: absolute;
        bottom: 60px;
        right: 0;
    }

    .hidden {
        display: none;
    }

    .chat-header {
        background-color: var(--heading-color, #2c4964);
        color: #ffffff;
        padding: 14px 16px;
    }

    .notify-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        background-color: #dc3545;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
    if (typeof window.jQuery === 'undefined') {
        document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
    }
    window.chatUserConfig = {
        routes: {
            send: "{{ route('chat.send') }}",
            unreadCount: "{{ route('chat.unread_count', ['senderId' => '__SID__']) }}",
            getMessages: "{{ route('chat.get', ['receiverId' => '__RID__']) }}"
        },
        pollInterval: 4000
    };
    function fillChatPrompt(text) {
        var input = document.getElementById('message');
        if (input) {
            input.value = text;
            input.focus();
        }
    }
</script>
<script src="{{ asset('js/chat/chat_user.js') }}"></script>
