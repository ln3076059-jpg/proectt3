<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generic_name',
        'brand_name',
        'slug',
        'category',
        'dosage_form',
        'strength',
        'unit',
        'price',
        'stock',
        'min_stock',
        'indications',
        'contraindications',
        'side_effects',
        'interactions',
        'dosage',
        'usage',
        'note',
        'manufacturer',
        'origin',
        'barcode',
        'is_prescription',
    ];
}
