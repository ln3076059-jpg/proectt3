<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|integer|exists:doctors,id',
            'service_id' => 'required|integer|exists:services,id',
            'time_slot_id' => 'nullable|integer|exists:time_slots,id',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|string',
            'medical_examination' => 'nullable|string',
            'note' => 'nullable|string|max:1000',
            'idempotency_key' => 'nullable|string|max:64',
            'booking_for' => 'nullable|string|in:self,relative',
            'relative_name' => 'nullable|required_if:booking_for,relative|string|max:255',
            'relative_relationship' => 'nullable|string|max:100',
            'relative_dob' => 'nullable|date',
            'relative_gender' => 'nullable|string|in:male,female,other',
            'payment_method_choice' => 'nullable|string|in:counter,vnpay',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Vui lòng chọn bác sĩ khám.',
            'doctor_id.exists' => 'Bác sĩ đã chọn không tồn tại trong hệ thống.',
            'service_id.required' => 'Vui lòng chọn dịch vụ khám.',
            'service_id.exists' => 'Dịch vụ đã chọn không tồn tại trong hệ thống.',
            'time_slot_id.exists' => 'Khung giờ khám không hợp lệ.',
            'appointment_date.date' => 'Ngày khám không đúng định dạng.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ];
    }
}
