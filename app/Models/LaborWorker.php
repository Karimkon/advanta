<?php
// app/Models/LaborWorker.php - UPDATED
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaborWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'name', 'phone', 'email', 'id_number', 'role',
        'daily_rate', 'monthly_rate', 'payment_frequency', 
        'start_date', 'end_date', 'status', 'nssf_number',
        'bank_name', 'bank_account', 'created_by'
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
        'daily_rate' => 0,
        'monthly_rate' => 0,
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(LaborPayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getCurrentRateAttribute()
    {
        return $this->payment_frequency === 'daily' ? $this->daily_rate : $this->monthly_rate;
    }

    public function getNssfAmountAttribute()
    {
        // Calculate NSSF amount (10% of payment)
        return $this->current_rate * 0.10;
    }
}