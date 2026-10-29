<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'image',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function symptoms()
    {
        return $this->hasMany(DiseaseSymptom::class);
    }
}
