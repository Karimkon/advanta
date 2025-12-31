<?php
// app/Models/Subcontractor.php - FIXED
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subcontractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contact_person', 'phone', 'email', 'specialization', 
        'address', 'tax_number', 'status'
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
}