$(function () {

    const cfg = window.ChatAdminConfig || {};
    const authId = Number(cfg.authId || 0);
    const messagesUrlTemplate = cfg.messagesUrlTemplate || '';
    const unreadByUserUrl = cfg.unreadByUserUrl || '';
    const sendUrl = cfg.sendUrl || '';
    const csrfToken = cfg.csrfToken || '';

    if (!authId || !messagesUrlTemplate || !unreadByUserUrl || !sendUrl) {
        return;
    }

    let receiverId = null;
    let pollTimer = null;

    function setUnreadBadges(byUser) {
        $('.unread-badge').each(function () {
            const uid = Number($(this).data('user-id'));
            const count = byUser && typeof byUser[uid] !== 'undefined' ? Number(byUser[uid]) : 0;
            if (count > 0) {
                $(this).text(String(count)).removeClass('d-none');
            } else {
                $(this).text('').addClass('d-none');
            }
        });
    }

    function loadUnreadBadges() {
        $.get(unreadByUserUrl, function (res) {
            setUnreadBadges((res && res.by_user) ? res.by_user : {});
        });
    }

    // Chọn bệnh nhân
    $('.user-item').on('click', function () {
        $('.user-item').removeClass('active');
        $(this).addClass('active');

        receiverId = $(this).data('id');
        $('#receiver_id').val(receiverId);
        $('#chat-form').removeClass('d-none');

        $(this).find('.unread-badge').text('').addClass('d-none');

        loadMessages();
        loadUnreadBadges();

        if (pollTimer) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(function () {
            if (receiverId) loadMessages(false);
            loadUnreadBadges();
        }, 2000);
    });

    // Gửi tin nhắn
    $('#chat-form').on('submit', function (e) {
        e.preventDefault();
        if (!receiverId || !$('#message').val().trim()) return;

        $.post(sendUrl, {
            _token: csrfToken,
            receiver_id: receiverId,
            message: $('#message').val()
        }, function () {
            $('#message').val('');
            loadMessages();
        });
    });

        // Load tin nhắn an toàn (Chống Stored XSS bằng textContent / jQuery .text())
        function loadMessages(scrollToBottom = true) {
            const url = messagesUrlTemplate.replace('__RID__', receiverId);
            $.get(url, function (messages) {
                const $container = $('#chat-messages');
                $container.empty();

                if (!messages || messages.length === 0) {
                    $container.html('<div class="text-muted text-center py-5"><i class="far fa-comments fa-3x text-gray-300 mb-3 d-block"></i>Chưa có tin nhắn nào trong cuộc trò chuyện này.</div>');
                    return;
                }

                messages.forEach(msg => {
                    const cls = msg.sender_id == authId
                        ? 'message-sent'
                        : 'message-received';

                    const $div = $('<div>').addClass('chat-message ' + cls).text(msg.message || '');
                    $container.append($div);
                });

                if (scrollToBottom && $container.length) {
                    $container.scrollTop($container[0].scrollHeight);
                }
            });
        }

    loadUnreadBadges();

});