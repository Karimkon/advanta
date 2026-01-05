<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['code','name','description','location','start_date','end_date','budget','status'];

    // Add date casting
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    protected $appends = ['total_spent', 'manager'];

    public function getTotalSpentAttribute()
    {
        // Calculate project payments through LPOs
        $projectPayments = \DB::table('payments')
            ->join('lpos', 'payments.lpo_id', '=', 'lpos.id')
            ->join('requisitions', 'lpos.requisition_id', '=', 'requisitions.id')
            ->where('requisitions.project_id', $this->id)
            ->where('payments.status', 'completed')
            ->sum('payments.amount');

        $projectExpenses = $this->expenses()->sum('amount');
        
        return (float) ($projectPayments + $projectExpenses);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Add stores relationship
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    // Get project manager
    public function getManagerAttribute()
    {
        return $this->users()->where('role', 'project_manager')->first();
    }
}