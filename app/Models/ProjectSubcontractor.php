<?php
// app/Models/ProjectSubcontractor.php - FIXED
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectSubcontractor extends Model
{
    use HasFactory;

    protected $table = 'project_subcontractors';

    protected $fillable = [
        'project_id', 'subcontractor_id', 'contract_number', 'work_description',
        'contract_amount', 'start_date', 'end_date', 'status', 'terms'
    ];

    protected $casts = [
        'contract_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function payments()
    {
        return $this->hasMany(SubcontractorPayment::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->contract_amount - $this->total_paid;
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->contract_amount == 0) return 0;
        return ($this->total_paid / $this->contract_amount) * 100;
    }
}