<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
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

    public function canPay(): bool
    {
        return $this->status === OrderStatusEnum::Pending;
    }

    public function hasPendingPayment(): bool
    {
        return $this->payments()->exists();
    }

    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => OrderStatusEnum::Completed,
        ]);
    }

    public function scopeNotCompleted(Builder $query): Builder
    {
        return $query->whereNot('status', OrderStatusEnum::Completed);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::Pending);
    }
}
