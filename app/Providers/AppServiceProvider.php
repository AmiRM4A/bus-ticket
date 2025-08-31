<?php

namespace App\Providers;

use App\Services\OrderService;
use App\Services\PassengerService;
use App\Services\PaymentService;
use App\Services\TripReservationService;
use App\Services\TripSeatService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TripSeatService::class, function () {
            return new TripSeatService(config('app.seat_reservation_ttl_minutes'));
        });

        $this->app->bind(PassengerService::class);

        $this->app->bind(PaymentService::class);

        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService($app->make(TripSeatService::class));
        });

        $this->app->bind(TripReservationService::class, function ($app) {
            return new TripReservationService(
                $app->make(PassengerService::class),
                $app->make(TripSeatService::class),
                $app->make(OrderService::class),
                $app->make(PaymentService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
