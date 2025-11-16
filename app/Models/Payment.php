<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['expense_id','lpo_id','supplier_id','paid_by','payment_method','status','amount','paid_on','reference','notes'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
