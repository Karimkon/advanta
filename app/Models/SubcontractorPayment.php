<?php
// app/Models/SubcontractorPayment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubcontractorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_subcontractor_id', 'payment_reference', 'payment_date',
        'amount', 'payment_type', 'description', 'paid_by', 'payment_method',
        'reference_number', 'notes', 'attachments'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'attachments' => 'array',
    ];

    public function projectSubcontractor()
    {
        return $this->belongsTo(ProjectSubcontractor::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function project()
    {
        return $this->hasOneThrough(Project::class, ProjectSubcontractor::class, 'id', 'id', 'project_subcontractor_id', 'project_id');
    }

    public function subcontractor()
    {
        return $this->hasOneThrough(Subcontractor::class, ProjectSubcontractor::class, 'id', 'id', 'project_subcontractor_id', 'subcontractor_id');
    }
}