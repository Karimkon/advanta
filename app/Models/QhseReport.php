<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QhseReport extends Model
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
        'report_date',
        'location',
        'department'
    ];

    protected $casts = [
        'attachments' => 'array',
        'report_date' => 'date'
    ];

    // Report types
    const TYPE_SAFETY = 'safety';
    const TYPE_QUALITY = 'quality';
    const TYPE_COMPANY_DOCUMENTS = 'companydocuments';
    const TYPE_HEALTH = 'health';
    const TYPE_ENVIRONMENT = 'environment';
    const TYPE_INCIDENT = 'incident';

    public function getReportTypeBadgeClass()
    {
        return match($this->report_type) {
            self::TYPE_SAFETY => 'bg-warning',
            self::TYPE_QUALITY => 'bg-info',
            self::TYPE_COMPANY_DOCUMENTS => 'bg-light text-dark',
            self::TYPE_HEALTH => 'bg-success',
            self::TYPE_ENVIRONMENT => 'bg-primary',
            self::TYPE_INCIDENT => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getReportTypeLabel()
    {
        return match($this->report_type) {
            self::TYPE_SAFETY => 'Safety Report',
            self::TYPE_QUALITY => 'Quality Report',
            self::TYPE_COMPANY_DOCUMENTS => 'Company Documents',
            self::TYPE_HEALTH => 'Health Report',
            self::TYPE_ENVIRONMENT => 'Environment Report',
            self::TYPE_INCIDENT => 'Incident Report',
            default => 'Unknown'
        };
    }

    public function getAttachmentsCount()
    {
        if (!$this->attachments) {
            return 0;
        }

        $attachments = $this->attachments;

        // If still a string (JSON not auto-decoded), decode it manually
        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true);
        }

        return is_array($attachments) ? count($attachments) : 0;
    }

    public function getAttachmentsArray()
    {
        if (!$this->attachments) {
            return [];
        }

        $attachments = $this->attachments;

        // If still a string (JSON not auto-decoded), decode it manually
        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true);
        }

        return is_array($attachments) ? $attachments : [];
    }
}