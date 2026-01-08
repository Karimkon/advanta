<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_staff_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
        'status',
        'month_for',
        'paid_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(OfficeStaff::class, 'office_staff_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
