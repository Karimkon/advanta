<?php
// app/Models/Subcontractor.php - Updated for Authentication
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Subcontractor extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'contact_person', 'phone', 'email', 'password', 'specialization',
        'address', 'tax_number', 'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    // FIXED: Use the correct relationship name
    public function projectSubcontractors()
    {
        return $this->hasMany(ProjectSubcontractor::class);
    }

    // Alias for API compatibility - projectContracts refers to the same thing
    public function projectContracts()
    {
        return $this->hasMany(ProjectSubcontractor::class);
    }

    // FIXED: Get projects through project_subcontractors
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_subcontractors')
                    ->withPivot('id', 'contract_number', 'work_description', 'contract_amount', 
                              'start_date', 'end_date', 'status', 'terms')
                    ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasManyThrough(SubcontractorPayment::class, ProjectSubcontractor::class);
    }

    public function getTotalContractsAmountAttribute()
    {
        return $this->projectSubcontractors()->sum('contract_amount');
    }

    public function getTotalPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->total_contracts_amount - $this->total_paid_amount;
    }

    /**
     * Get requisitions made by this subcontractor
     */
    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    /**
     * Get active project contracts
     */
    public function activeContracts()
    {
        return $this->projectSubcontractors()->where('status', 'active');
    }

    /**
     * Check if subcontractor has an active contract on a project
     */
    public function hasActiveContractOn($projectId)
    {
        return $this->projectSubcontractors()
            ->where('project_id', $projectId)
            ->where('status', 'active')
            ->exists();
    }
}