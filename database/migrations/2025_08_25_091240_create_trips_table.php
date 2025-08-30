<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')
                ->constrained('buses')
                ->cascadeOnDelete();
            $table->foreignId('from_province_id')
                ->index()
                ->constrained('provinces')
                ->cascadeOnDelete();
            $table->foreignId('to_province_id')
                ->index()
                ->constrained('provinces')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('total_seats');
            $table->decimal('price_per_seat', 8, 2);
            $table->unsignedSmallInteger('reserved_seats_count')->default(0);

            $table->date('trip_date')->index();
            $table->time('departure_time');
            $table->timestamp('arrived_at')->nullable();
            $table->timestamps();

            $table->index(['from_province_id', 'to_province_id', 'trip_date']);
        });

        // Constraint to check if reserved seats are always less than/equal to total seats
        DB::statement('ALTER TABLE trips 
        ADD CONSTRAINT chk_reserved_seats_count 
        CHECK (reserved_seats_count <= total_seats)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
