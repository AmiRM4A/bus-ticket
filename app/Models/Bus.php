<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'driver_id',
        'from',
        'to',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'from');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'to');
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
