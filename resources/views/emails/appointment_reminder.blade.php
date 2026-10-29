<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhắc nhở lịch hẹn</title>
</head>
<body>
    {{-- Greeting --}}
    <p>Chào {{ optional($appointment->patient)->name ?? 'Quý khách' }},</p>

    {{-- Reminder Message --}}
    <p>Đây là email nhắc nhở rằng bạn có lịch hẹn vào ngày mai.</p>

    {{-- Appointment Details --}}
    <ul>
        <li><strong>Thời gian:</strong> {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') : 'N/A' }}</li>
        <li><strong>Bác sĩ:</strong> {{ optional(optional($appointment->doctor)->user)->name ?? 'N/A' }}</li>
        <li><strong>Dịch vụ:</strong> {{ optional($appointment->service)->name ?? 'N/A' }}</li>
        <li><strong>Trạng thái:</strong> {{ $appointment->status ?? 'N/A' }}</li>
    </ul>

    {{-- Contact Information --}}
    <p>Nếu bạn không thể tham dự, vui lòng liên hệ sớm để được hỗ trợ.</p>

    {{-- Closing --}}
    <p>Trân trọng,<br>Phòng khám</p>
</body>
</html>
