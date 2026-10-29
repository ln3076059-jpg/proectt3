<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSymptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'symptom_name',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
