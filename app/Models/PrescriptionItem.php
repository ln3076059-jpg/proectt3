<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'unit',
        'usage',
        'note',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'prescription_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
