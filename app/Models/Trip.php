<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'from_province_id',
        'to_province_id',
        'total_seats',
        'price_per_seat',
        'reserved_seats_count',
        'trip_date',
        'departure_time',
        'arrived_at',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'arrived_at' => 'datetime',
        'price_per_seat' => 'decimal:2',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'from_province_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'to_province_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TripReservation::class, 'trip_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(TripSeat::class, 'trip_id');
    }
}
