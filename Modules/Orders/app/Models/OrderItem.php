<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Orders\Database\Factories\OrderItemFactory;
use Modules\Passengers\Models\Passenger;
use Modules\Trips\Models\TripSeat;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'trip_seat_id',
        'passenger_id',
        'price',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tripSeat(): BelongsTo
    {
        return $this->belongsTo(TripSeat::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
