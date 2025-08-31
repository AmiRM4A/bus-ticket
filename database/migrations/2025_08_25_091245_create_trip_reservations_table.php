<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->index()->constrained('passengers');
            $table->foreignId('trip_id')->index()->constrained('trips');
            $table->foreignId('trip_seat_id')->index()->constrained('trip_seats');
            $table->timestamps();

            $table->unique(['trip_id', 'trip_seat_id'], 'unique_seat_reservation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_reservations');
    }
};
