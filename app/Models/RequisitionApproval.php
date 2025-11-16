<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequisitionApproval extends Model
{
    use HasFactory;

    protected $fillable = ['requisition_id','approved_by','role','action','comment','approved_amount'];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
