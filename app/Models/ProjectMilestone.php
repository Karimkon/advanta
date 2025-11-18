<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description', 
        'due_date',
        'completed_at',
        'status',
        'cost_estimate',
        'actual_cost',
        'completion_percentage',
        'progress_notes',
        'photo_path',        
        'photo_caption'      
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'cost_estimate' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Status options
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DELAYED = 'delayed';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_DELAYED => 'Delayed',
        ];
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'in_progress' => 'bg-info',
            'completed' => 'bg-success',
            'delayed' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'completed';
    }

    public function getProgressPercentage()
    {
        return $this->completion_percentage ?? 0;
    }

    // NEW: Get photo URL
    public function getPhotoUrl()
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    // NEW: Check if has photo
    public function hasPhoto()
    {
        return !empty($this->photo_path);
    }

    public static function getStatusBadgeClassStatic($status)
{
    return match($status) {
        'pending' => 'bg-warning',
        'in_progress' => 'bg-info',
        'completed' => 'bg-success',
        'delayed' => 'bg-danger',
        default => 'bg-secondary',
    };
}
}