<?php

namespace Modules\Trips\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Buses\Models\BusSeat;
use Modules\Orders\Models\OrderItem;
use Modules\Trips\Database\Factories\TripSeatFactory;
use Modules\Trips\Enums\TripSeatStatusEnum;

class TripSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'bus_seat_id',
        'status',
        'expires_at',
        'reserved_gender',
    ];

    protected $casts = [
        'status' => TripSeatStatusEnum::class,
        'reserved_gender' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function busSeat(): BelongsTo
    {
        return $this->belongsTo(BusSeat::class, 'bus_seat_id', 'id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', TripSeatStatusEnum::AVAILABLE);
    }

    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', TripSeatStatusEnum::RESERVED);
    }

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', TripSeatStatusEnum::SOLD);
    }

    public function orderItems(): HasOne
    {
        return $this->hasOne(OrderItem::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TripSeatFactory
    {
        return TripSeatFactory::new();
    }
}
