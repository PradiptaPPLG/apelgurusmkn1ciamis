<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'class_name', 'year'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}