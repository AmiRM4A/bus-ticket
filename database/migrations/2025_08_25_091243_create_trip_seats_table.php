<?php

use App\Enums\TripSeatStatusEnum;
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
        Schema::create('trip_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')
                ->constrained('trips')
                ->cascadeOnDelete();
            $table->foreignId('bus_seat_id')
                ->constrained('bus_seats')
                ->cascadeOnDelete();
            $table->enum('status', TripSeatStatusEnum::values())
                ->default(TripSeatStatusEnum::AVAILABLE->value);
            $table->timestamp('expires_at')->nullable();
            $table->enum('reserved_gender', [0, 1])
                ->nullable()
                ->comment('0 = Male, 1 = Female');
            $table->timestamps();

            $table->unique(['trip_id', 'bus_seat_id']);

            $table->index(['trip_id', 'status']);
            $table->index('expires_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_seats');
    }
};
