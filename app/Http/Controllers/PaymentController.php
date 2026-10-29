<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    // Xử lý thanh toán VNPay cho lịch hẹn mới (Thống nhất qua AppointmentService)
    public function vnpay_payment(Request $request)
    {
        try {
            Log::info('VNPay Payment Request:', $request->all());

            // Validate dữ liệu đầu vào (Hỗ trợ cả chuẩn TimeSlot mới lẫn ca khám truyền thống)
            $validated = $request->validate([
                'patient_id' => 'nullable|exists:patients,id',
                'department_id' => 'nullable|exists:departments,id',
                'doctor_id' => 'required|exists:doctors,id',
                'service_id' => 'required|exists:services,id',
                'time_slot_id' => 'nullable|exists:time_slots,id',
                'appointment_date' => 'required_without:time_slot_id|nullable|date',
                'appointment_time' => 'nullable',
                'medical_examination' => 'required_without:time_slot_id|nullable|in:Ca sáng (07:30 - 11:30),Ca chiều (13:00 - 17:00)',
                'note' => 'nullable|string',
                'booking_for' => 'nullable|in:self,relative',
                'relative_name' => 'nullable|string|max:255',
                'relative_dob' => 'nullable|date',
                'relative_gender' => 'nullable|string|max:50',
                'relative_relationship' => 'nullable|string|max:100',
                'idempotency_key' => 'nullable|string|max:100',
            ]);

            $service = Service::with('department')->findOrFail($validated['service_id']);
            $doctor = Doctor::findOrFail($validated['doctor_id']);

            // Đồng bộ khoa theo dịch vụ nếu user không gửi khoa (hoặc để validate chéo)
            $departmentId = $validated['department_id'] ?? $service->department_id;

            // Nếu có gửi department_id thì bắt buộc phải khớp với department của dịch vụ
            if (!empty($validated['department_id']) && (string) $validated['department_id'] !== (string) $service->department_id) {
                return back()->with('error', 'Dịch vụ không thuộc khoa đã chọn. Vui lòng chọn lại.')->withInput();
            }

            // Bác sĩ cũng phải thuộc đúng khoa (theo khoa đã chọn/suy ra)
            if ($departmentId && (string) $doctor->department_id !== (string) $departmentId) {
                return back()->with('error', 'Bác sĩ không thuộc khoa của dịch vụ đã chọn. Vui lòng chọn lại.')->withInput();
            }

            // Kiểm tra thời gian đặt lịch theo ca truyền thống (nếu không truyền time_slot_id)
            if (empty($validated['time_slot_id']) && !empty($validated['medical_examination'])) {
                $appointmentDateTime = Carbon::parse($validated['appointment_date']);

                if (strpos($validated['medical_examination'], 'Ca sáng') !== false) {
                    $appointmentDateTime->setTime(7, 30, 0);
                    $timeString = '07:30';
                    $shiftName = 'sáng';
                } elseif (strpos($validated['medical_examination'], 'Ca chiều') !== false) {
                    $appointmentDateTime->setTime(13, 0, 0);
                    $timeString = '13:00';
                    $shiftName = 'chiều';
                } else {
                    return back()->with('error', 'Ca khám không hợp lệ.')->withInput();
                }

                $hoursUntilAppointment = now()->diffInHours($appointmentDateTime, false);

                if ($hoursUntilAppointment < 5) {
                    $dateString = Carbon::parse($validated['appointment_date'])->format('d/m/Y');

                    if ($hoursUntilAppointment < 0) {
                        return back()->with(
                            'error',
                            'Không thể đặt lịch cho ca khám đã qua. ' .
                            'Ca '.$shiftName.' ngày '.$dateString.' ('.$timeString.') đã bắt đầu hoặc đã qua.'
                        )->withInput();
                    } else {
                        $hoursRemaining = round($hoursUntilAppointment, 1);
                        return back()->with(
                            'error',
                            'Bạn phải đặt lịch trước ít nhất 5 giờ so với thời gian bắt đầu ca khám. ' .
                            'Ca '.$shiftName.' ngày '.$dateString.' ('.$timeString.') chỉ còn '.$hoursRemaining.' giờ nữa. ' .
                            'Vui lòng chọn ca khám khác hoặc ngày khác.'
                        )->withInput();
                    }
                }
            }

            // Xác định User thực hiện đặt lịch (Chống lỗ hổng IDOR giả mạo patient_id của người khác)
            $authUser = $request->user();
            if (!$authUser) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt lịch và thanh toán trực tuyến.');
            }

            $isAdmin = method_exists($authUser, 'hasRole') && $authUser->hasRole('admin');
            if (!$isAdmin) {
                $ownPatient = $authUser->patient ?: Patient::firstOrCreate(
                    ['user_id' => $authUser->id],
                    [
                        'name' => $authUser->name,
                        'phone' => $authUser->phone,
                        'email' => $authUser->email,
                    ]
                );
                if (!empty($validated['patient_id']) && (int) $validated['patient_id'] !== (int) $ownPatient->id) {
                    abort(403, 'Bạn không có quyền khởi tạo thanh toán cho hồ sơ bệnh nhân của người khác.');
                }
                $validated['patient_id'] = $ownPatient->id;
            }

            // Khởi tạo lịch hẹn thông qua AppointmentService (đảm bảo khóa hàng, trừ slot, sinh mã PK và lưu Audit Trail)
            $validated['payment_status'] = Appointment::PAYMENT_FAILED;
            $appointment = $this->appointmentService->bookAppointment($validated, $authUser);

            // Tạo mã giao dịch (TxnRef) và cập nhật lịch hẹn
            $txnRef = $this->getTxnRef($appointment->id);
            $appointment->update(['transaction_ref' => $txnRef]);

            // Build VNPay URL
            $vnpUrl = $this->buildVnpayUrl($appointment, $service->price, $request, $txnRef);

            Log::info('VNPay Redirect URL', ['url' => $vnpUrl, 'appointment_code' => $appointment->appointment_code]);

            return redirect()->away($vnpUrl);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?: 'Dữ liệu đặt lịch không hợp lệ.';
            return back()->with('error', $firstError)->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('VNPay Payment Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Lỗi khi khởi tạo thanh toán: '.$e->getMessage());
        }
    }

    // Xử lý callback từ VNPay sau khi thanh toán
    public function vnpay_return(Request $request)
    {
        $input = $request->all();
        Log::info('VNPay Return Data', $input);

        $vnp_HashSecret = trim((string) config('services.vnpay.hash_secret'));
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? '';
        unset($input['vnp_SecureHash'], $input['vnp_SecureHashType']);

        // Xác thực chữ ký bảo mật VNPay
        ksort($input);
        $hashData = '';
        $i = 0;
        foreach ($input as $key => $value) {
            if (substr($key, 0, 4) === 'vnp_') {
                if ($i === 1) {
                    $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . '=' . urlencode($value);
                    $i = 1;
                }
            }
        }

        $calculatedHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($calculatedHash !== $vnp_SecureHash) {
            Log::warning('VNPay checksum mismatch', [
                'expected' => $calculatedHash,
                'received' => $vnp_SecureHash,
            ]);
            return redirect()->route('home')->with('error', 'Chữ ký bảo mật thanh toán không hợp lệ (Checksum Mismatch).');
        }

        $responseCode = $input['vnp_ResponseCode'] ?? null;
        $orderInfo = $input['vnp_OrderInfo'] ?? '';

        // Kiểm tra response code
        if (!$responseCode) {
            return redirect()->route('home')->with('error', 'Dữ liệu phản hồi không hợp lệ.');
        }

        // Lấy appointment ID từ order info
        if (!preg_match('/#(\d+)/', $orderInfo, $matches)) {
            return redirect()->route('home')->with('error', 'Không thể xác định mã lịch hẹn.');
        }

        $appointmentId = $matches[1];
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin lịch hẹn.');
        }

        // Chống xử lý trùng callback
        if ($appointment->payment_status === 'success') {
            return redirect()
                ->route('appointments.show', $appointment->id)
                ->with('info', 'Lịch hẹn này đã được ghi nhận thanh toán trước đó.');
        }

        // Xác thực số tiền thanh toán từ VNPay khớp với viện phí gốc của lịch hẹn (Chống can thiệp vnp_Amount)
        $expectedAmount = (float) ($appointment->total_amount ?? $appointment->total ?? optional($appointment->service)->price ?? 0);
        if (isset($input['vnp_Amount']) && $expectedAmount > 0) {
            $returnedAmount = (float) $input['vnp_Amount'] / 100;
            if (abs($returnedAmount - $expectedAmount) >= 1) {
                Log::warning('VNPay amount mismatch', [
                    'appointment_id' => $appointment->id,
                    'expected' => $expectedAmount,
                    'returned' => $returnedAmount,
                ]);
                return redirect()
                    ->route('appointments.show', $appointment->id)
                    ->with('error', 'Số tiền phản hồi từ cổng thanh toán không khớp với viện phí của lịch hẹn.');
            }
        }

        // Xử lý thanh toán thành công
        if ($responseCode === '00') {
            $paidAmount = isset($input['vnp_Amount'])
                ? ((float) $input['vnp_Amount'] / 100)
                : $expectedAmount;

            $updateData = [
                'payment_status' => 'success',
                'transaction_id' => $input['vnp_TransactionNo'] ?? null,
                'transaction_ref' => $input['vnp_TxnRef'] ?? null,
                'payment_method' => 'VNPay - ' . ($input['vnp_BankCode'] ?? 'Cổng thanh toán điện tử'),
                'total_amount' => $paidAmount,
                'total' => $paidAmount,
                'paid_at' => now(),
            ];

            $appointment->update($updateData);

            // Ghi nhật ký kiểm vết thanh toán VNPay thành công
            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => $appointment->status,
                'to_status' => $appointment->status,
                'changed_by_user_id' => auth()->id() ?? optional($appointment->patient)->user_id,
                'action_role' => 'patient',
                'note' => 'Thanh toán trực tuyến VNPay thành công (' . number_format($paidAmount, 0, ',', '.') . ' VNĐ - Mã GD: ' . ($input['vnp_TransactionNo'] ?? $input['vnp_TxnRef'] ?? 'N/A') . ')',
            ]);

            return redirect()
                ->route('appointments.show', $appointment->id)
                ->with('success', 'Thanh toán VNPay thành công! Lịch hẹn của bạn đã được xác nhận thanh toán.');
        }

        // Xử lý thanh toán thất bại
        $errorMessage = $responseCode === '24'
            ? 'Giao dịch thanh toán đã bị hủy bởi người dùng.'
            : 'Thanh toán thất bại. Mã lỗi phản hồi: ' . $responseCode;

        $appointment->update(['payment_status' => 'failed']);

        return redirect()->route('appointments.show', $appointment->id)->with('error', $errorMessage);
    }

    /**
     * Xử lý Webhook Server-to-Server IPN (Instant Payment Notification) từ VNPay
     * Đảm bảo cập nhật trạng thái thanh toán ngay cả khi trình duyệt của bệnh nhân bị đóng trước khi redirect về vnpay_return.
     */
    public function vnpay_ipn(Request $request)
    {
        $input = $request->all();
        $vnp_HashSecret = trim((string) config('services.vnpay.hash_secret'));
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? '';
        unset($input['vnp_SecureHash'], $input['vnp_SecureHashType']);

        ksort($input);
        $hashData = '';
        $i = 0;
        foreach ($input as $key => $value) {
            if (substr($key, 0, 4) === 'vnp_') {
                if ($i === 1) {
                    $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . '=' . urlencode($value);
                    $i = 1;
                }
            }
        }

        $calculatedHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if (!hash_equals($calculatedHash, (string) $vnp_SecureHash)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $orderInfo = $input['vnp_OrderInfo'] ?? '';
        $txnRef = $input['vnp_TxnRef'] ?? '';
        $appointment = null;

        if (preg_match('/#(\d+)/', $orderInfo, $matches)) {
            $appointment = Appointment::with(['service', 'patient'])->find((int) $matches[1]);
        } elseif ($txnRef !== '') {
            $appointment = Appointment::with(['service', 'patient'])->where('transaction_ref', $txnRef)->first();
        }

        if (!$appointment) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        $expectedAmount = (float) ($appointment->total_amount ?? $appointment->total ?? optional($appointment->service)->price ?? 0);
        if (isset($input['vnp_Amount']) && $expectedAmount > 0) {
            $returnedAmount = (float) $input['vnp_Amount'] / 100;
            if (abs($returnedAmount - $expectedAmount) >= 1) {
                return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
            }
        }

        if ($appointment->payment_status === 'success') {
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        $responseCode = $input['vnp_ResponseCode'] ?? '';
        $transactionStatus = $input['vnp_TransactionStatus'] ?? $responseCode;

        if ($responseCode === '00' && $transactionStatus === '00') {
            $paidAmount = isset($input['vnp_Amount']) ? ((float) $input['vnp_Amount'] / 100) : $expectedAmount;
            $appointment->update([
                'payment_status' => 'success',
                'transaction_id' => $input['vnp_TransactionNo'] ?? null,
                'transaction_ref' => $txnRef ?: $appointment->transaction_ref,
                'payment_method' => 'VNPay IPN - ' . ($input['vnp_BankCode'] ?? 'Cổng thanh toán điện tử'),
                'total_amount' => $paidAmount,
                'total' => $paidAmount,
                'paid_at' => now(),
            ]);

            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => $appointment->status,
                'to_status' => $appointment->status,
                'changed_by_user_id' => optional($appointment->patient)->user_id,
                'action_role' => 'system',
                'note' => 'Xác nhận thanh toán tự động qua VNPay IPN (' . number_format($paidAmount, 0, ',', '.') . ' VNĐ)',
            ]);
        } else {
            $appointment->update(['payment_status' => 'failed']);
        }

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    // Khởi tạo thanh toán cho một lịch hẹn đã tồn tại
    public function checkout(Request $request, Appointment $appointment)
    {
        try {
            $user = $request->user();
            if ($user && !$user->hasRole('admin') && (!$user->patient || (int) $user->patient->id !== (int) $appointment->patient_id)) {
                abort(403, 'Bạn không có quyền thanh toán lịch hẹn này.');
            }

            // Kiểm tra lịch hẹn đã thanh toán chưa
            if ($appointment->payment_status === 'success') {
                return redirect()->route('appointments.show', $appointment->id)
                    ->with('error', 'Lịch hẹn này đã được thanh toán.');
            }

            // Xác định số tiền cần thanh toán
            $amount = null;

            if (!is_null($appointment->total_amount) && $appointment->total_amount > 0) {
                $amount = (float) $appointment->total_amount;
            } elseif (!is_null($appointment->total) && $appointment->total > 0) {
                $amount = (float) $appointment->total;
            } else {
                $service = Service::find($appointment->service_id);
                $amount = $service ? (float) $service->price : 0;
            }

            if ($amount <= 0) {
                return redirect()->route('appointments.show', $appointment->id)
                    ->with('error', 'Không xác định được số tiền thanh toán cho lịch hẹn.');
            }

            // Tạo mã giao dịch và cập nhật lịch hẹn
            $txnRef = $this->getTxnRef($appointment->id);
            $appointment->update([
                'transaction_ref' => $txnRef,
                'payment_status' => 'failed',
            ]);

            // Build URL và chuyển hướng
            $vnpUrl = $this->buildVnpayUrl($appointment, $amount, $request, $txnRef);
            Log::info('VNPay Checkout Redirect URL', ['url' => $vnpUrl]);

            return redirect()->away($vnpUrl);
        } catch (\Exception $e) {
            Log::error('VNPay Checkout Error', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('appointments.show', $appointment->id)
                ->with('error', 'Không thể khởi tạo thanh toán: '.$e->getMessage());
        }
    }

    // Tạo mã giao dịch (Transaction Reference) cho VNPay
    private function getTxnRef($appointmentId)
    {
        return 'APP'.$appointmentId.'_'.time();
    }

    // Build URL thanh toán VNPay theo chuẩn VNPay
    private function buildVnpayUrl(Appointment $appointment, $price, Request $request, $txnRef)
    {
        // Lấy cấu hình VNPay từ config/services.php
        $vnp_TmnCode = trim((string) config('services.vnpay.tmn_code'));
        $vnp_HashSecret = trim((string) config('services.vnpay.hash_secret'));
        $vnp_Url = rtrim((string) config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'), '?');
        $vnp_Returnurl = trim((string) (config('services.vnpay.return_url') ?: route('vnpay.return')));

        // Tính số tiền thanh toán đúng theo giá dịch vụ niêm yết (VNPay yêu cầu nhân 100)
        $vnp_Amount = (int) round((float) $price * 100);

        // Chuẩn bị tham số VNPay
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $request->ip(),
            'vnp_Locale' => $request->input('language', 'vn'),
            'vnp_OrderInfo' => 'Thanh toan lich kham #'.$appointment->id,
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_TxnRef' => $txnRef,
            'vnp_ExpireDate' => now()->addMinutes(15)->format('YmdHis'),
        ];

        // Thêm bank code nếu có
        if ($bankCode = $request->input('bankCode')) {
            $params['vnp_BankCode'] = $bankCode;
        }

        // Sắp xếp tham số theo thứ tự alphabet
        ksort($params);

        // Build hash data và query string
        $hashData = '';
        $query = '';
        $i = 0;

        foreach ($params as $key => $value) {
            if ($i == 1) {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        // Tạo chữ ký bảo mật
        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $finalUrl = $vnp_Url.'?'.$query.'vnp_SecureHash='.$vnpSecureHash;

        Log::debug('VNPay Signature Debug', [
            'hash_data' => $hashData,
            'secure_hash' => $vnpSecureHash,
            'final_url' => $finalUrl,
        ]);

        return $finalUrl;
    }

    // Hệ số giảm giá: mặc định thanh toán 80%, nếu cùng tháng sinh giảm còn 70%
    private function calculateDiscount(?string $birthdate): float
    {
        $defaultDiscount = 0.8;

        if (! $birthdate) {
            return $defaultDiscount;
        }

        $isBirthdayMonth = Carbon::parse($birthdate)->isSameMonth(now());

        return $isBirthdayMonth ? 0.7 : $defaultDiscount;
    }
}
