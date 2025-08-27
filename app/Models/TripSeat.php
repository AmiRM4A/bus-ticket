<?php

namespace App\Models;

use App\Enums\TripSeatStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'bus_seat_id',
        'status',
    ];

    protected $casts = [
        'status' => TripSeatStatusEnum::class,
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
}
