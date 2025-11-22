<?php
// app/Models/LaborPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaborPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'labor_worker_id', 'payment_reference', 'payment_date',
        'period_start', 'period_end', 'gross_amount', 'nssf_amount',
        'amount', 'net_amount', 'days_worked', 'description', 
        'paid_by', 'payment_method', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'nssf_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function laborWorker()
    {
        return $this->belongsTo(LaborWorker::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function project()
    {
        return $this->hasOneThrough(Project::class, LaborWorker::class, 'id', 'id', 'labor_worker_id', 'project_id');
    }
}