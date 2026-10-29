{{-- AI Chat Widget Container --}}
<div id="ai-chat-widget" style="position:fixed; left:24px; bottom:24px; width:360px; max-width:92vw; border:1px solid #EBEBF0; border-radius:12px; overflow:hidden; font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-shadow:0 8px 30px rgba(0,0,0,.15); background:#fff; z-index:1050;">
    {{-- Chat Header --}}
    <div id="chat-header" style="background:#1A94FF; color:white; padding:12px 14px; display:flex; align-items:center; justify-content:space-between;">
        <div style="font-weight:600; display:flex; align-items:center; gap:8px; font-size:14px;">
            <span style="font-size:18px;">🤖</span>
            <div>
                <div>AI Tư Vấn Khám Bệnh</div>
                <div style="font-size:11px; font-weight:400; opacity:0.9;">Phòng khám Thái Thịnh</div>
            </div>
        </div>
        <button id="toggle-chat"
                aria-label="Thu gọn"
                style="background:rgba(255,255,255,0.2); color:#fff; border:none; width:26px; height:26px; border-radius:50%; font-size:14px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            ▾
        </button>
    </div>

    {{-- Chat Container --}}
    <div id="chat-container" style="display:flex; flex-direction:column; height:420px;">
        {{-- Chat Messages Box --}}
        <div id="chat-box" style="flex:1; overflow-y:auto; padding:12px; background:#F5F5FA;">
            <div id="messages">
                <div style="margin:8px 0; display:flex; justify-content:flex-start;">
                    <div style="max-width:85%; padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5; background:#FFFFFF; color:#27272A; border:1px solid #EBEBF0; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                        Xin chào bạn! Tôi là trợ lý AI của <strong>Phòng khám Đa khoa 125 Thái Thịnh</strong>. Hãy mô tả triệu chứng sức khỏe của bạn để tôi gợi ý khoa khám và dịch vụ phù hợp nhất nhé!
                    </div>
                </div>
            </div>
        </div>

        {{-- Chat Input Area --}}
        <div id="chat-input" style="display:flex; align-items:end; gap:8px; border-top:1px solid #EBEBF0; padding:10px; background:#fff;">
            <textarea id="user-input"
                      placeholder="Mô tả triệu chứng (vd: ho, sốt, đau khớp...)"
                      rows="2"
                      style="flex:1; border:1px solid #DDDDE3; padding:8px 10px; resize:none; outline:none; border-radius:8px; font-family:'Inter', sans-serif; font-size:13px;"></textarea>
            <button id="send-btn"
                    style="flex:0 0 auto; height:42px; padding:0 16px; background:#1A94FF; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; transition:0.2s;"
                    onmouseover="this.style.background='#0D5CB6'"
                    onmouseout="this.style.background='#1A94FF'">
                Gửi
            </button>
        </div>
    </div>
</div>

{{-- AI Chat Widget Script --}}
<script>
    // Responsive adjustment for narrow viewports
    try {
        const mq = window.matchMedia('(max-width: 420px)');
        const ai = document.getElementById('ai-chat-widget');
        function adjust() {
            ai.style.bottom = mq.matches ? '90px' : '24px';
        }
        adjust();
        mq.addEventListener ? mq.addEventListener('change', adjust) : mq.addListener(adjust);
    } catch (e) {}

    // Widget elements
    const widget = document.getElementById('ai-chat-widget');
    const toggleBtn = document.getElementById('toggle-chat');
    const messagesDiv = widget.querySelector('#messages');
    const input = widget.querySelector('#user-input');
    const sendBtn = widget.querySelector('#send-btn');
    const container = widget.querySelector('#chat-container');

    // Toggle chat container
    let collapsed = false;
    toggleBtn.addEventListener('click', () => {
        collapsed = !collapsed;
        container.style.display = collapsed ? 'none' : 'flex';
        toggleBtn.textContent = collapsed ? '▴' : '▾';
    });

    // Create message bubble
    function bubble(content, align = 'left', tone = 'ai') {
        const wrap = document.createElement('div');
        wrap.style.margin = '8px 0';
        wrap.style.display = 'flex';
        wrap.style.justifyContent = align === 'right' ? 'flex-end' : 'flex-start';

        const span = document.createElement('div');
        span.style.maxWidth = '85%';
        span.style.padding = '10px 14px';
        span.style.borderRadius = '12px';
        span.style.whiteSpace = 'pre-wrap';
        span.style.wordBreak = 'break-word';
        span.style.fontSize = '13px';
        span.style.lineHeight = '1.5';

        if (tone === 'user') {
            span.style.background = '#1A94FF';
            span.style.color = '#fff';
            span.style.borderBottomRightRadius = '4px';
        } else {
            span.style.background = '#ffffff';
            span.style.color = '#27272A';
            span.style.border = '1px solid #EBEBF0';
            span.style.borderBottomLeftRadius = '4px';
            span.style.boxShadow = '0 1px 3px rgba(0,0,0,0.04)';
        }

        span.innerHTML = content;
        wrap.appendChild(span);
        messagesDiv.appendChild(wrap);
        const chatBox = widget.querySelector('#chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
        return wrap;
    }

    // Render suggestions (services and departments)
    function renderSuggestions(sugs) {
        if (!sugs) return;

        const hasServices = Array.isArray(sugs.services) && sugs.services.length;
        const hasDepts = Array.isArray(sugs.departments) && sugs.departments.length;
        if (!hasServices && !hasDepts) return;

        let html = '<div style="margin-top:6px">';

        // Services section
        if (hasServices) {
            html += '<div style="font-weight:600; color:#1A94FF; margin:6px 0 4px; font-size:12px;">🩺 GỢI Ý DỊCH VỤ PHÙ HỢP:</div>';
            html += '<ul style="padding-left:16px; margin:0; font-size:12px;">'
                + sugs.services.map(s => `<li style="margin:3px 0;"><strong>${s.name}</strong>${s.price !== null && s.price !== undefined ? ` - <span style='color:#FF424E; font-weight:700;'>${Number(s.price).toLocaleString('vi-VN')} đ</span>` : ''}</li>`).join('')
                + '</ul>';
        }

        // Departments section
        if (hasDepts) {
            html += '<div style="font-weight:600; color:#00AB56; margin:8px 0 4px; font-size:12px;">🏥 CHUYÊN KHOA KHUYÊN KHÁM:</div>';
            html += '<ul style="padding-left:16px; margin:0; font-size:12px;">'
                + sugs.departments.map(d => `<li style="margin:2px 0;">${d.name}</li>`).join('')
                + '</ul>';
        }

        // Action buttons with prefilled parameters
        let createLink = '/appointments/create';
        if (hasServices && sugs.services[0]) {
            const firstSvc = sugs.services[0];
            const dId = firstSvc.department_id || (hasDepts && sugs.departments[0] ? sugs.departments[0].id : '');
            createLink = `/appointments/create?department_id=${dId}&service_id=${firstSvc.id || ''}`;
        } else if (hasDepts && sugs.departments[0]) {
            createLink = `/appointments/create?department_id=${sugs.departments[0].id || ''}`;
        }

        html += '<div style="margin-top:10px; display:flex; gap:6px; flex-wrap:wrap;">'
            + `<a href="/services" style="text-decoration:none; background:#1A94FF; color:#fff; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:600;">Xem chi tiết dịch vụ</a>`
            + `<a href="${createLink}" style="text-decoration:none; background:#00AB56; color:#fff; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:600;"><i class="fas fa-calendar-check" style="margin-right:4px;"></i>Đặt lịch khám ngay</a>`
            + '</div>';
        html += '</div>';

        bubble(html, 'left', 'ai');
    }

    // Send message to AI
    async function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        bubble(message, 'right', 'user');
        input.value = '';

        const loading = bubble('<i>Đang phân tích triệu chứng...</i>', 'left', 'ai');

        try {
            const res = await fetch('/api/chat/ai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message })
            });
            const data = await res.json();
            loading.remove();
            bubble(data.reply || 'Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn. Bạn vui lòng mô tả chi tiết hơn triệu chứng nhé!', 'left', 'ai');
            renderSuggestions(data.suggestions);
        } catch (e) {
            loading.remove();
            bubble('⚠️ Có lỗi xảy ra, vui lòng thử lại sau giây lát.', 'left', 'ai');
        }
    }

    // Send button click handler
    sendBtn.addEventListener('click', sendMessage);

    // Enter key handler (Shift+Enter for new line)
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
</script>
