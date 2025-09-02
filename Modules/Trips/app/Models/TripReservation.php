<?php

namespace Modules\Trips\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Passengers\Models\Passenger;
use Modules\Trips\Database\Factories\TripReservationFactory;

class TripReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id',
        'trip_id',
        'trip_seat_id',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function tripSeat(): BelongsTo
    {
        return $this->belongsTo(TripSeat::class, 'trip_seat_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TripReservationFactory
    {
        return TripReservationFactory::new();
    }
}
