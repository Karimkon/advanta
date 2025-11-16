<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','contact_person','phone','email','address','category','rating','notes','status'];

    public function lpos()
    {
        return $this->hasMany(Lpo::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
      // Check if supplier is active
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

      // Get rating stars (for display)
    public function getRatingStarsAttribute()
    {
        $stars = '';
        $fullStars = floor($this->rating);
        $halfStar = ($this->rating - $fullStars) >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
                $stars .= '<i class="bi bi-star-fill text-warning"></i>';
            } elseif ($halfStar && $i == $fullStars + 1) {
                $stars .= '<i class="bi bi-star-half text-warning"></i>';
            } else {
                $stars .= '<i class="bi bi-star text-warning"></i>';
            }
        }
        
        return $stars;
    }
}
