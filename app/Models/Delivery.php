<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = ['lpo_id','supplier_id','received_by','delivered_at','status','delivery_note'];

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }
}
