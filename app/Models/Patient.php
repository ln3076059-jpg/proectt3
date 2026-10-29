<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'birthdate',
        'gender',
        'address',
        'phone',
        'insurance_number',
        'avatar',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Tự động điền name và email từ user khi tạo patient mới.
     */
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($patient) {
    //         if ($patient->user) {
    //             // Nếu có user và name chưa được điền, lấy từ user
    //             if (empty($patient->name)) {
    //                 $patient->name = $patient->user->name;
    //             }
    //             // Nếu có user và email chưa được điền, lấy từ user
    //             if (empty($patient->email)) {
    //                 $patient->email = $patient->user->email;
    //             }
    //         }
    //     });
    // }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
