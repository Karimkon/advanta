<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','phone','password','role','shop_id','back_debt'
    ];

    protected $hidden = ['password','remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'back_debt' => 'decimal:2',
    ];

    // relationships
    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps()->withPivot('role_on_project');
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class, 'requested_by');
    }

    public function approvals()
    {
        return $this->hasMany(RequisitionApproval::class, 'approved_by');
    }

    public function receivedDeliveries()
    {
        return $this->hasMany(Delivery::class, 'received_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'paid_by');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
