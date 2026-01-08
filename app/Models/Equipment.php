<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'name',
        'model',
        'category',
        'description',
        'value',
        'purchase_date',
        'condition',
        'location',
        'project_id',
        'images',
        'serial_number',
        'added_by',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'purchase_date' => 'date',
        'images' => 'array',
    ];

    // Equipment Categories
    const CATEGORY_DUMP_TRUCK = 'dump_truck';
    const CATEGORY_EXCAVATOR = 'excavator';
    const CATEGORY_CRANE = 'crane';
    const CATEGORY_CEMENT_MIXER = 'cement_mixer';
    const CATEGORY_CONCRETE_VIBRATOR = 'concrete_vibrator';
    const CATEGORY_GENERATOR = 'generator';
    const CATEGORY_SCAFFOLDING = 'scaffolding';
    const CATEGORY_COMPACTOR = 'compactor';
    const CATEGORY_CUTTING_MACHINE = 'cutting_machine';
    const CATEGORY_LOADER = 'loader';
    const CATEGORY_OTHER = 'other';

    // Status Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_DISPOSED = 'disposed';

    // Condition Constants
    const CONDITION_NEW = 'new';
    const CONDITION_GOOD = 'good';
    const CONDITION_FAIR = 'fair';
    const CONDITION_POOR = 'poor';
    const CONDITION_NEEDS_REPAIR = 'needs_repair';

    /**
     * Get all available categories with labels
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_DUMP_TRUCK => 'Dump Truck',
            self::CATEGORY_EXCAVATOR => 'Excavator',
            self::CATEGORY_CRANE => 'Crane',
            self::CATEGORY_CEMENT_MIXER => 'Cement Mixer',
            self::CATEGORY_CONCRETE_VIBRATOR => 'Concrete Vibrator',
            self::CATEGORY_GENERATOR => 'Power Generator',
            self::CATEGORY_SCAFFOLDING => 'Scaffolding System',
            self::CATEGORY_COMPACTOR => 'Road Compactor/Roller',
            self::CATEGORY_CUTTING_MACHINE => 'Cutting Machine',
            self::CATEGORY_LOADER => 'Loader',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    /**
     * Get all available statuses with labels
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_MAINTENANCE => 'Under Maintenance',
            self::STATUS_DISPOSED => 'Disposed',
        ];
    }

    /**
     * Get all available conditions with labels
     */
    public static function getConditions(): array
    {
        return [
            self::CONDITION_NEW => 'New',
            self::CONDITION_GOOD => 'Good',
            self::CONDITION_FAIR => 'Fair',
            self::CONDITION_POOR => 'Poor',
            self::CONDITION_NEEDS_REPAIR => 'Needs Repair',
        ];
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::getCategories()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get condition label
     */
    public function getConditionLabelAttribute(): string
    {
        return self::getConditions()[$this->condition] ?? ucfirst($this->condition);
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImageAttribute(): ?string
    {
        if (!$this->images || count($this->images) === 0) {
            return null;
        }
        
        $image = $this->images[0];
        
        // Handle case where image might be nested array or non-string
        if (is_array($image)) {
            return $image[0] ?? null;
        }
        
        return $image;
    }

    /**
     * Relationship: Equipment belongs to a Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship: Equipment was added by a User
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Scope: Active equipments only
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by project
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Get total value of all active equipments
     */
    public static function getTotalValue(): float
    {
        return static::active()->sum('value');
    }
}
