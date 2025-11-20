<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'title',
        'description',
        'staff_name',
        'staff_email',
        'access_code',
        'attachments',
        'report_date'
    ];

    protected $casts = [
        'attachments' => 'array',
        'report_date' => 'date'
    ];

    // Report types
    const TYPE_DAILY = 'daily';
    const TYPE_WEEKLY = 'weekly';

    public function getReportTypeBadgeClass()
    {
        return match($this->report_type) {
            self::TYPE_DAILY => 'bg-primary',
            self::TYPE_WEEKLY => 'bg-success',
            default => 'bg-secondary'
        };
    }

    public function getAttachmentsCount()
    {
        return $this->attachments ? count($this->attachments) : 0;
    }
}