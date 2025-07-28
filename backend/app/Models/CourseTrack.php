<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'estimated_duration_hours',
        'difficulty_level',
        'course_order',
        'active'
    ];

    protected $casts = [
        'course_order' => 'array',
        'active' => 'boolean',
        'estimated_duration_hours' => 'integer'
    ];

    // Relationships
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_track_courses');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    // Helper methods
    public function getOrderedCourses()
    {
        if (!$this->course_order) {
            return $this->courses;
        }

        $courseIds = $this->course_order;
        $courses = $this->courses()->whereIn('id', $courseIds)->get();
        
        // Sort courses by the order specified
        return $courses->sortBy(function ($course) use ($courseIds) {
            return array_search($course->id, $courseIds);
        });
    }

    public function getTotalDurationAttribute()
    {
        return $this->courses()->sum('workload_hours') ?? $this->estimated_duration_hours;
    }

    public function getCoursesCountAttribute()
    {
        return $this->courses()->count();
    }

    public function getCompletionPercentage($userId)
    {
        $totalCourses = $this->courses()->count();
        if ($totalCourses === 0) return 0;

        $completedCourses = $this->courses()
            ->whereHas('enrollments', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'completed');
            })->count();

        return round(($completedCourses / $totalCourses) * 100, 2);
    }
}
