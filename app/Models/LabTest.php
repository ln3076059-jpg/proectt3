<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    protected $fillable = [
        'medical_record_id',
        'department_id',
        'test_type_id',
        'test_name',
        'image',
        'images',
        'note',
        'requested_by',
        'uploaded_by',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function record()
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function test_type()
    {
        return $this->belongsTo(TestType::class, 'test_type_id');
    }

    /**
     * Đường dẫn bảo mật kiểm tra phân quyền trước khi xem/tải kết quả xét nghiệm CLS
     */
    public function getSecureImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        return route('lab_tests.attachment', ['labTest' => $this->id]);
    }
}
