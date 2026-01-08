<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'department',
        'phone',
        'email',
        'salary',
        'status',
        'joined_date',
        'created_by'
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'joined_date' => 'date',
    ];

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
