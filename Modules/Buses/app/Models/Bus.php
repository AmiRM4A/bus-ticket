<?php

namespace Modules\Buses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Drivers\Models\Driver;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'model',
        'plate',
        'seats_count',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(BusSeat::class, 'bus_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BusReservation::class, 'bus_id');
    }
}
