<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'requisition_id',
        'lpo_id',
        'type',
        'description', 
        'amount',
        'incurred_on',
        'recorded_by',
        'status',
        'notes'
    ];

    protected $casts = [
        'incurred_on' => 'date',
        'amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }
}