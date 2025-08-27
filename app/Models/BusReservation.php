<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Bus;
use App\Models\BusSeat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusReservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'bus_id',
        'seat_id'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(BusSeat::class, 'seat_id');
    }
}
