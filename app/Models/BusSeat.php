<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bus_id',
        'row',
        'column',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BusReservation::class, 'seat_id');
    }

    public function tripSeats(): HasMany
    {
        return $this->hasMany(TripSeat::class);
    }
}
