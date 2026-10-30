<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    // Lấy user hiện tại theo đúng guard của phân vùng URL (tránh lẫn phiên giữa tab Admin và tab Bệnh nhân)
    private function authUser(): ?User
    {
        if (request()->is('admin/*') || request()->routeIs('admin.*')) {
            if (Auth::guard('web_admin')->check()) {
                return Auth::guard('web_admin')->user();
            }
            if (Auth::guard('web')->check() && Auth::guard('web')->user()->hasRole('admin')) {
                return Auth::guard('web')->user();
            }
            return null;
        }

        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }

        if (Auth::guard('web_admin')->check()) {
            return Auth::guard('web_admin')->user();
        }

        return null;
    }

    // Lấy id user hiện tại
    private function authId(): ?int
    {
        $user = $this->authUser();

        return $user ? $user->id : null;
    }

    // Hiển thị giao diện chat cho admin
    public function adminChat()
    {
        $user = $this->authUser();

        if (!$user) {
            abort(401);
        }

        // Lấy danh sách bệnh nhân kèm hồ sơ & lịch hẹn gần nhất, ưu tiên người có tương tác tin nhắn mới nhất
        $users = User::where('id', '!=', $user->id)
            ->whereHas('role', function ($query) {
                $query->where('name', 'patient');
            })
            ->with(['role', 'patient.appointments' => function ($q) {
                $q->with(['doctor.user', 'service'])->latest('id')->limit(1);
            }])
            ->orderByDesc(
                Message::select('created_at')
                    ->where(function ($mq) use ($user) {
                        $mq->whereColumn('messages.sender_id', 'users.id')
                            ->where('messages.receiver_id', $user->id);
                    })
                    ->orWhere(function ($mq) use ($user) {
                        $mq->whereColumn('messages.receiver_id', 'users.id')
                            ->where('messages.sender_id', $user->id);
                    })
                    ->latest('created_at')
                    ->limit(1)
            )
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $authId = $user->id;

        return view('admin.chat.admin', compact('users', 'authId'));
    }

    // Điều hướng trang chat theo role (admin hoặc user thường)
    public function index()
    {
        $user = $this->authUser(); // Lấy thông tin người dùng hiện tại

        if (!$user) {
            abort(401);
        }

        // Trường hợp người dùng hiện tại là admin
        if ($user->role && $user->role->name === 'admin') {
            return $this->adminChat();
        }

        // Trường hợp người dùng là user thông thường
        // -> Mặc định chỉ có thể chat với admin
        $admin = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })
            ->with('role')
            ->first();

        // Kiểm tra nếu không có admin trong hệ thống
        if (!$admin) {
            return redirect()->route('home')
                ->with('error', 'Hiện tại chưa có quản trị viên để hỗ trợ. Vui lòng thử lại sau.');
        }

        // Trả về view chat cho user, truyền id của admin để xác định người nhận
        return view('chat.user', ['receiverId' => $admin->id]);
    }

    // Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        $sender = $this->authUser();

        if (!$sender) {
            abort(401);
        }

        // Kiểm tra dữ liệu hợp lệ
        $request->validate([
            'receiver_id' => 'required|exists:users,id', // ID người nhận phải tồn tại
            'message' => 'required|string', // Nội dung tin nhắn là chuỗi ký tự
        ]);
        // Lấy thông tin người nhận
        $receiver = User::with('role')->findOrFail($request->receiver_id);

        // Phân quyền: user chỉ chat với admin; admin chỉ chat với user
        if ($sender->role && in_array($sender->role->name, ['admin', 'leader'])) {
            if (!$receiver->role || !in_array($receiver->role->name, ['patient', 'user'])) {
                abort(403);
            }
        } else {
            if (!$receiver->role || !in_array($receiver->role->name, ['admin', 'leader'])) {
                abort(403);
            }
        }

        // Lưu tin nhắn vào CSDL
        Message::create([
            'sender_id' => $sender->id, // ID người gửi = user đang đăng nhập
            'receiver_id' => $request->receiver_id, // ID người nhận được truyền từ request
            'message' => $request->message, // Nội dung tin nhắn
        ]);

        // Trả về phản hồi JSON cho AJAX
        return response()->json(['status' => 'success']);
    }

    // Đếm tổng số tin nhắn chưa đọc gửi tới admin hiện tại
    public function adminUnreadCount()
    {
        $user = $this->authUser();

        if (!$user) {
            abort(401);
        }

        if (!$user->role || $user->role->name !== 'admin') {
            abort(403);
        }

        $count = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Đếm số tin nhắn chưa đọc theo từng bệnh nhân gửi tới admin
    public function adminUnreadByUser()
    {
        $user = $this->authUser();

        if (!$user) {
            abort(401);
        }

        if (!$user->role || $user->role->name !== 'admin') {
            abort(403);
        }

        $rows = Message::query()
            ->select('sender_id', DB::raw('COUNT(*) as unread_count'))
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->get();

        $byUser = [];

        foreach ($rows as $row) {
            $byUser[(int) $row->sender_id] = (int) $row->unread_count;
        }

        return response()->json(['by_user' => $byUser]);
    }

    // Đếm tin nhắn chưa đọc của user hiện tại từ 1 người gửi cụ thể
    public function userUnreadCount($senderId)
    {
        $user = $this->authUser();

        if (!$user) {
            abort(401);
        }

        $count = Message::where('sender_id', $senderId)
            ->where('receiver_id', $this->authId())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Lấy danh sách tin nhắn giữa người dùng hiện tại và người được chọn
    public function getMessages($receiverId)
    {
        // Lấy thông tin người gửi
        $sender = $this->authUser();

        if (!$sender) {
            abort(401);
        }

        // Lấy thông tin người nhận
        $receiver = User::with('role')->findOrFail($receiverId);

        // Phân quyền: user chỉ chat với admin; admin chỉ chat với user
        if ($sender->role && in_array($sender->role->name, ['admin', 'leader'])) {
            if (!$receiver->role || !in_array($receiver->role->name, ['patient', 'user'])) {
                abort(403);
            }
        } else {
            if (!$receiver->role || !in_array($receiver->role->name, ['admin', 'leader'])) {
                abort(403);
            }
        }

        // Đánh dấu tin nhắn đã đọc khi mở hội thoại
        Message::where('sender_id', $receiverId)
            ->where('receiver_id', $this->authId())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Lấy toàn bộ tin nhắn giữa 2 người
        $messages = Message::where(function ($q) use ($receiverId) {
            // Điều kiện: user hiện tại là người gửi và receiver là người nhận
            $q->where('sender_id', $this->authId())
                ->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($q) use ($receiverId) {
                // Điều kiện ngược lại: receiver gửi và user hiện tại là người nhận
                $q->where('sender_id', $receiverId)
                    ->where('receiver_id', $this->authId());
            })
            ->orderBy('created_at', 'asc') // Sắp xếp theo thứ tự thời gian
            ->get();

        // Trả về danh sách tin nhắn dạng JSON cho frontend
        return response()->json($messages);
    }
}
