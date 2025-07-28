<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    // Relationships
    public function sessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')->withPivot('status', 'enrolled_at', 'completed_at');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWithUpcomingSessions($query)
    {
        return $query->with(['sessions' => function ($query) {
            $query->where('start_datetime', '>=', now())->orderBy('start_datetime');
        }]);
    }
}
