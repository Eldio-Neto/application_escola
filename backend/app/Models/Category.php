<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_categories');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Helper methods
    public function getCoursesCountAttribute()
    {
        return $this->courses()->count();
    }

    public function getActiveCoursesCountAttribute()
    {
        return $this->courses()->active()->count();
    }
}
