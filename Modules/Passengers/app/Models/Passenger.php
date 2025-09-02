<?php

namespace Modules\Passengers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'birth_date',
        'gender',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'gender' => 'int',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(TripReservation::class, 'passenger_id');
    }
}
