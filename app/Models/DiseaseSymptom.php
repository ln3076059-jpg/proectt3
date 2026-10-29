<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseaseSymptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'disease_id',
        'symptom_name',
    ];

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }
}
