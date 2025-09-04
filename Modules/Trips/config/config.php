<?php

return [
    'name' => 'Trips',

    /*
    |--------------------------------------------------------------------------
    | Seat Reservation Time To Live (TTL)
    |--------------------------------------------------------------------------
    |
    | The number of minutes a seat reservation is held before it expires if
    | not confirmed (e.g., by completing a payment).
    |
    */

    'seat_reservation_ttl_minutes' => env('SEAT_RESERVATION_TTL_MINUTES', 15),
];
