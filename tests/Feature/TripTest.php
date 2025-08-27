<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Trip;
use App\Models\Bus;
use App\Models\Province;

class TripTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a trip can be created with a price_per_seat.
     */
    public function test_trip_can_be_created_with_price_per_seat(): void
    {
        // Create required related models
        $bus = Bus::factory()->create();
        $origin = Province::factory()->create();
        $destination = Province::factory()->create();

        // Create a trip with price_per_seat
        $trip = Trip::factory()->create([
            'bus_id' => $bus->id,
            'from_province_id' => $origin->id,
            'to_province_id' => $destination->id,
            'price_per_seat' => 75.50,
        ]);

        // Assert the trip was created successfully
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'price_per_seat' => 75.50,
        ]);

        // Assert the model has the correct value
        $this->assertEquals(75.50, $trip->price_per_seat);
    }

    /**
     * Test that price_per_seat is cast to decimal.
     */
    public function test_price_per_seat_is_cast_to_decimal(): void
    {
        // Create required related models
        $bus = Bus::factory()->create();
        $origin = Province::factory()->create();
        $destination = Province::factory()->create();

        // Create a trip
        $trip = Trip::factory()->create([
            'bus_id' => $bus->id,
            'from_province_id' => $origin->id,
            'to_province_id' => $destination->id,
            'price_per_seat' => 100.99,
        ]);

        // Check that the price_per_seat is cast correctly
        $this->assertEquals('100.99', $trip->price_per_seat);
        $this->assertEquals(100.99, (float) $trip->price_per_seat);
    }
}
