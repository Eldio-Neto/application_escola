<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'max_participants',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Accessors
    public function getFormattedStartDateAttribute()
    {
        return $this->start_datetime->format('d/m/Y H:i');
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_datetime->format('d/m/Y H:i');
    }

    public function getDurationAttribute()
    {
        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_datetime', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now());
    }

    // Helper methods
    public function isUpcoming()
    {
        return $this->start_datetime > now();
    }

    public function isPast()
    {
        return $this->end_datetime < now();
    }

    public function isActive()
    {
        return $this->start_datetime <= now() && $this->end_datetime >= now();
    }
}
