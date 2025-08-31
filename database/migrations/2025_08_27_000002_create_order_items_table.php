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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('trip_seat_id')
                ->constrained('trip_seats')
                ->cascadeOnDelete();
            $table->foreignId('passenger_id')
                ->constrained('passengers')
                ->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['order_id', 'trip_seat_id']);

            $table->index(['trip_seat_id', 'passenger_id']);

            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
