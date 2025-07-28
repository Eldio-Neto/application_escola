<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'usage_limit',
        'used_count',
        'usage_limit_per_user',
        'valid_from',
        'valid_until',
        'active',
        'category_ids',
        'course_ids'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'active' => 'boolean',
        'category_ids' => 'array',
        'course_ids' => 'array'
    ];

    // Relationships
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'coupon_courses');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    // Helper methods
    public function isValid()
    {
        return $this->active && 
               $this->valid_from <= now() && 
               $this->valid_until >= now();
    }

    public function canBeUsed()
    {
        if (!$this->isValid()) return false;
        
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }

    public function calculateDiscount($amount)
    {
        if (!$this->canBeUsed()) return 0;
        
        if ($this->minimum_amount && $amount < $this->minimum_amount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return ($amount * $this->value) / 100;
        }

        return min($this->value, $amount);
    }

    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    public function getFormattedValueAttribute()
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        }
        
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }
}
