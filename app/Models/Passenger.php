<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TripReservation;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'birth_date'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(TripReservation::class, 'passenger_id');
    }
}