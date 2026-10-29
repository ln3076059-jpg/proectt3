{{-- Email Header --}}
<h2>Xin chào {{ $appointment->patient->name ?? 'Quý khách' }}</h2>

{{-- Confirmation Message --}}
<p>Lịch hẹn của bạn đã được <strong>xác nhận</strong>.</p>

{{-- Appointment Details --}}
<ul>
    <li><strong>Dịch vụ:</strong> {{ $appointment->service->name ?? 'N/A' }}</li>
    <li><strong>Bác sĩ:</strong> {{ $appointment->doctor->user->name ?? 'N/A' }}</li>
    <li><strong>Thời gian:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</li>
    <li><strong>Ca khám:</strong> {{ $appointment->medical_examination ?? '---' }}</li>

    @php
        use Carbon\Carbon;

        $price = $appointment->total ?? ($appointment->service->price ?? 0);
        $birthdate = $appointment->patient->birthdate ?? null;

        // Default payment: 80%
        $prepayPercent = 80;
        $discountPercent = 0;

        // If birthday in current month → total payment = 70%
        if ($birthdate && Carbon::parse($birthdate)->format('m') == now()->format('m')) {
            $discountPercent = 10; // 10% discount
            $prepayPercent = 70;   // Total payment is only 70% of original price
        }

        // Calculate amounts
        $discountAmount = $price * ($discountPercent / 100);
        $finalPrice = $price - $discountAmount; // Price after discount (for display only)
        $prepayAmount = $price * ($prepayPercent / 100); // Payment according to actual percentage
        $remainAmount = max(0, $finalPrice - $prepayAmount);
    @endphp

    <li><strong>Giá gốc:</strong> {{ number_format($price, 0, ',', '.') }} đ</li>

    @if ($discountPercent > 0)
        <li><strong>Giảm giá:</strong> {{ $discountPercent }}% ({{ number_format($discountAmount, 0, ',', '.') }} đ)</li>
        <li style="color:green; font-weight:bold; margin-top:8px; list-style:none;">
            🎉 Sinh nhật trong tháng! Bạn chỉ cần thanh toán <strong>{{ $prepayPercent }}%</strong> giá gốc (đã bao gồm ưu đãi sinh nhật).
        </li>
    @else
        <li style="list-style:none;"><strong>Thanh toán trước {{ $prepayPercent }}%</strong> tổng giá dịch vụ.</li>
    @endif

    <li><strong>Thanh toán trước:</strong>
        <span style="color:blue; font-weight:bold;">
            {{ number_format($prepayAmount, 0, ',', '.') }} đ
        </span>
    </li>

    <li><strong>Còn lại:</strong> {{ number_format($remainAmount, 0, ',', '.') }} đ <strong>Thanh toán tại bệnh viện</strong></li>

    <li><strong>Ghi chú:</strong> {{ $appointment->note ?? 'Không có' }}</li>
</ul>

{{-- Closing Message --}}
<p>Cảm ơn bạn đã đặt lịch tại hệ thống!</p>
