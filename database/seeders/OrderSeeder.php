<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory(10)->create()->each(function ($order) {
            Payment::factory(1)->create(['order_id' => $order->id]);
            OrderItem::factory(3)->create(['order_id' => $order->id]);
        });
    }
}
