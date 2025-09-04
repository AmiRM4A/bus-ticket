<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Orders\Database\Factories\OrderFactory;
use Modules\Orders\Enums\OrderStatusEnum;
use Modules\Payments\Models\Payment;
use Modules\Trips\Models\Trip;
use Modules\Users\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'data',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTripIdAttribute()
    {
        return $this->data['trip_id'] ?? null;
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function canPay(): bool
    {
        return $this->status === OrderStatusEnum::Pending;
    }

    public function hasPendingPayment(): bool
    {
        return $this->payments()->exists();
    }

    public function updateStatus(OrderStatusEnum $status): bool
    {
        return $this->update([
            'status' => $status,
        ]);
    }

    public function markAsCompleted(): bool
    {
        return $this->updateStatus(OrderStatusEnum::Completed);
    }

    public function markAsCancelled(): bool
    {
        return $this->updateStatus(OrderStatusEnum::Cancelled);
    }

    public function scopeStatus(Builder $query, OrderStatusEnum $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeNotCompleted(): Builder
    {
        return $this->status(OrderStatusEnum::Completed);
    }

    public function scopePending(): Builder
    {
        return $this->status(OrderStatusEnum::Pending);
    }

    public function scopeCancelled(): Builder
    {
        return $this->status(OrderStatusEnum::Cancelled);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function hasAnyPayment(): bool
    {
        return $this->payments()->exists();
    }

    public function hasAnyItem(): bool
    {
        return $this->orderItems()->exists();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function getTotalAmountAttribute()
    {
        return $this->orderItems()->sum('price');
    }
}
