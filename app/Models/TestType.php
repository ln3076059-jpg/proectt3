<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestType extends Model
{
    protected $fillable = ['name', 'description', 'department_id', 'created_by'];

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
