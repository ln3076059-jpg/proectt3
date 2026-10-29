/**
 * Chat Widget cho User
 * Xử lý chat giữa user và admin
 */
(function() {
    'use strict';

    // Lấy config từ window object (được set từ Blade template)
    const config = window.chatUserConfig || {};
    
    // Cache DOM elements
    const $chatBox = $('#chat-box');
    const $toggleBtn = $('#chat-toggle');
    const $closeBtn = $('#chat-close');
    const $messagesDiv = $('#chat-messages');
    const $notifyDot = $('#chat-notify');
    const $receiverIdInput = $('#receiver_id');
    const $messageInput = $('#message');
    const $chatForm = $('#chat-form');

    // State
    let receiverId = $receiverIdInput.val();
    let lastMessageCount = 0;
    let pollTimer = null;

    /**
     * Khởi tạo chat widget.
     */
    function init() {
        if (!config.routes) {
            return;
        }

        bindEvents();

        const currentUserId = parseInt($chatForm.data('user-id')) || null;
        if (currentUserId) {
            updateUnreadBadge();
            startPolling();
        }
    }

    /**
     * Yêu cầu quyền thông báo trình duyệt.
     */
    function requestNotificationPermission() {
        if (typeof Notification !== 'undefined' && Notification.permission !== 'granted') {
            Notification.requestPermission();
        }
    }

    /**
     * Gắn các sự kiện cho chat widget.
     */
    function bindEvents() {
        $toggleBtn.on('click', handleToggleChat);
        $closeBtn.on('click', handleCloseChat);
        $chatForm.on('submit', handleSendMessage);
    }

    /**
     * Xử lý mở/đóng chat box.
     */
    function handleToggleChat() {
        $chatBox.toggleClass('hidden');
        $notifyDot.text('').addClass('d-none');
        
        if (!$chatBox.hasClass('hidden')) {
            receiverId = $receiverIdInput.val();
            loadMessages();
        }
    }

    /**
     * Xử lý đóng chat box.
     */
    function handleCloseChat() {
        $chatBox.addClass('hidden');
    }

    /**
     * Xử lý gửi tin nhắn.
     */
    function handleSendMessage(e) {
        e.preventDefault();
        
        const message = $messageInput.val().trim();
        if (!message || !receiverId) {
            return;
        }

        $.ajax({
            url: config.routes.send,
            method: 'POST',
            data: {
                _token: $('input[name=_token]').val(),
                receiver_id: receiverId,
                message: message
            },
            success: function() {
                $messageInput.val('');
                loadMessages();
            },
            error: function() {
                alert('Không thể gửi tin nhắn. Vui lòng thử lại.');
            }
        });
    }

    /**
     * Cập nhật badge số tin nhắn chưa đọc.
     */
    function updateUnreadBadge() {
        receiverId = $receiverIdInput.val();
        
        if (!receiverId) {
            $notifyDot.text('').addClass('d-none');
            return;
        }

        const url = config.routes.unreadCount.replace('__SID__', receiverId);
        
        $.get(url)
            .done(function(res) {
                const count = res && typeof res.count !== 'undefined' ? Number(res.count) : 0;
                
                if (count > 0 && $chatBox.hasClass('hidden')) {
                    $notifyDot.text(String(count)).removeClass('d-none');
                } else {
                    $notifyDot.text('').addClass('d-none');
                }
            })
            .fail(function() {
                // Silent fail để không làm gián đoạn UX
            });
    }

    /**
     * Tải danh sách tin nhắn.
     */
    function loadMessages() {
        receiverId = $receiverIdInput.val();
        
        if (!receiverId) {
            showErrorMessage('Không tìm thấy Admin để chat. Vui lòng đăng nhập lại hoặc liên hệ quản trị.');
            return;
        }

        const url = config.routes.getMessages.replace('__RID__', receiverId);
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(messages) {
                handleNewMessages(messages);
                displayMessages(messages);
            },
            error: function(xhr) {
                handleLoadError(xhr);
            }
        });
    }

    /**
     * Xử lý tin nhắn mới (thông báo, âm thanh).
     */
    function handleNewMessages(messages) {
        if (messages.length > lastMessageCount && $chatBox.hasClass('hidden')) {
            updateUnreadBadge();
            playNotificationSound();
            showBrowserNotification('Bạn có tin nhắn mới từ Admin!');
        }
        lastMessageCount = messages.length;
    }

    /**
     * Hiển thị danh sách tin nhắn.
     */
    function displayMessages(messages) {
        $messagesDiv.empty();
        
        if (messages.length === 0) {
            $messagesDiv.html('<p class="text-muted text-center mb-0">Chưa có tin nhắn nào.</p>');
            return;
        }

        const currentUserId = parseInt($chatForm.data('user-id')) || null;
        
        messages.forEach(function(msg) {
            const isMine = msg.sender_id == currentUserId;
            const msgClass = isMine ? 'text-end text-primary' : 'text-start text-dark';
            const sender = isMine ? 'Bạn' : 'Admin';
            
            const $p = $('<p>').addClass(msgClass + ' mb-2');
            const $strong = $('<strong>').text(sender + ': ');
            $p.append($strong);
            $p.append(document.createTextNode(msg.message || ''));
            $messagesDiv.append($p);
        });
        
        scrollToBottom();
    }

    /**
     * Cuộn xuống tin nhắn mới nhất.
     */
    function scrollToBottom() {
        const messagesElement = $messagesDiv[0];
        if (messagesElement) {
            messagesElement.scrollTop = messagesElement.scrollHeight;
        }
    }

    /**
     * Xử lý lỗi khi tải tin nhắn.
     */
    function handleLoadError(xhr) {
        const status = xhr && xhr.status ? String(xhr.status) : '';
        let errorText = 'Không tải được tin nhắn.';
        
        if (status === '401') {
            errorText = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
        } else if (status === '403') {
            errorText = 'Bạn không có quyền chat. Vui lòng kiểm tra quyền tài khoản.';
        }
        
        showErrorMessage(errorText);
    }

    /**
     * Hiển thị thông báo lỗi.
     */
    function showErrorMessage(text) {
        $messagesDiv.html(`<p class="text-danger text-center mb-0">${text}</p>`);
    }

    /**
     * Phát âm thanh thông báo.
     */
    function playNotificationSound() {
        try {
            const audio = new Audio('/sounds/notify.mp3');
            audio.play().catch(function() {
                // Ignore audio errors
            });
        } catch (e) {
            // Ignore audio errors
        }
    }

    /**
     * Hiển thị thông báo trình duyệt.
     */
    function showBrowserNotification(text) {
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            new Notification('Tin nhắn mới', { body: text });
        }
    }

    /**
     * Escape HTML để tránh XSS.
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Bắt đầu polling để cập nhật tin nhắn và badge (tự động tạm dừng khi tab ẩn để tiết kiệm tài nguyên).
     */
    function startPolling() {
        const pollInterval = config.pollInterval || 4000;
        
        pollTimer = setInterval(function() {
            if (document.hidden) {
                return;
            }
            if (!$chatBox.hasClass('hidden')) {
                loadMessages();
            } else {
                updateUnreadBadge();
            }
        }, pollInterval);
    }

    /**
     * Dừng polling.
     */
    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    // Khởi tạo khi DOM ready
    $(document).ready(init);

    // Cleanup khi page unload
    $(window).on('beforeunload', stopPolling);
})();

