<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'amount',
        'payment_method',
        'status',
        'getnet_payment_id',
        'getnet_order_id',
        'boleto_url',
        'boleto_barcode',
        'due_date',
        'paid_at',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'gateway_response' => 'array',
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

    public function enrollment()
    {
        return $this->hasOne(Enrollment::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeBoleto($query)
    {
        return $query->where('payment_method', 'boleto');
    }

    public function scopeCreditCard($query)
    {
        return $query->where('payment_method', 'credit_card');
    }

    // Helper methods
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isBoleto()
    {
        return $this->payment_method === 'boleto';
    }

    public function isCreditCard()
    {
        return $this->payment_method === 'credit_card';
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido'
        };
    }

    public function getPaymentMethodTextAttribute()
    {
        return match($this->payment_method) {
            'boleto' => 'Boleto',
            'credit_card' => 'Cartão de Crédito',
            default => 'Desconhecido'
        };
    }
}
