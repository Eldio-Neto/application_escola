<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'course_id',
        'price',
        'quantity',
        'expires_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scopes
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Helper methods
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function extend($hours = 24)
    {
        $this->expires_at = now()->addHours($hours);
        $this->save();
    }

    // Static methods
    public static function getCartItems($sessionId = null, $userId = null)
    {
        $query = self::with('course')->active();
        
        if ($userId) {
            $query->forUser($userId);
        } elseif ($sessionId) {
            $query->forSession($sessionId);
        }
        
        return $query->get();
    }

    public static function getCartTotal($sessionId = null, $userId = null)
    {
        $items = self::getCartItems($sessionId, $userId);
        return $items->sum('total');
    }

    public static function clearCart($sessionId = null, $userId = null)
    {
        $query = self::query();
        
        if ($userId) {
            $query->forUser($userId);
        } elseif ($sessionId) {
            $query->forSession($sessionId);
        }
        
        return $query->delete();
    }
}
