<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
