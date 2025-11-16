<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = ['project_id','title','description','due_date','completed_at','status','cost_estimate'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
