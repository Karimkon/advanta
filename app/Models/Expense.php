<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['project_id','requisition_id','lpo_id','type','description','amount','incurred_on','recorded_by','status','notes'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
