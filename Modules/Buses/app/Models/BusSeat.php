<?php

namespace Modules\Buses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Buses\Database\Factories\BusSeatFactory;
use Modules\Trips\Models\TripSeat;

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

    public function tripSeats(): HasMany
    {
        return $this->hasMany(TripSeat::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): BusSeatFactory
    {
        return BusSeatFactory::new();
    }

    public function getSeatNumberAttribute(): string
    {
        return "$this->column$this->row";
    }
}
