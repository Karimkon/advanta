<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends Model
{
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'device_token',
        'device_type',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the parent tokenable model (User or Client)
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for iOS devices
     */
    public function scopeIos($query)
    {
        return $query->where('device_type', 'ios');
    }

    /**
     * Scope for Android devices
     */
    public function scopeAndroid($query)
    {
        return $query->where('device_type', 'android');
    }
}
