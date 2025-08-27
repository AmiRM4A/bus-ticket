<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidSeatForReservation;
use App\Http\Requests\DeleteTripReservationRequest;
use App\Http\Requests\StoreTripReservationRequest;
use App\Http\Resources\TripIndexResource;
use App\Http\Resources\TripShowResource;
use App\Models\Trip;
use App\Models\User;
use App\Services\TripReservationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Throwable;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TripController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);
        $trips = Trip::with(['origin:id,name', 'destination:id,name', 'bus:id,model,plate,seats_count'])->paginate($perPage, page: $page);
        return $this->success(TripIndexResource::collection($trips));
    }

    public function store(StoreTripReservationRequest $request, int $trip_id): JsonResponse
    {
        # Order items won't be created & trip_seats won't be updated. the amount of payment is 0 too
        $trip = Trip::findOrFail($trip_id);
        try {
            $user = $request->user() ?? User::first();
            $reservationData = $request->validated('passengers');
            $order = TripReservationService::createReservation($user, $trip, $reservationData);

            return $this->success([
                'message' => 'Trip reservation successful.',
                'order_id' => $order->id,
            ], HttpResponse::HTTP_CREATED);

        } catch (InvalidSeatForReservation $e) {
            return $this->failure($e->getMessage(), HttpResponse::HTTP_BAD_REQUEST);
        } catch (QueryException $e) {
            // Log the exception for debugging purposes
            Log::error("Database error during trip reservation: " . $e->getMessage());
            return $this->failure('A database error occurred during reservation. Please try again later.');
        } catch (Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
            // Catch any other unexpected exceptions
            Log::error("Unexpected error during trip reservation: " . $e->getMessage());
            return $this->failure('An unexpected error occurred during reservation. Please try again later.');
        }
    }

    public function show(int $trip_id): JsonResponse
    {
        $trip = Trip::with(['origin:id,name', 'destination:id,name', 'bus:id,model,plate,seats_count', 'seats.busSeat:name'])->findOrFail($trip_id);
        return $this->success(new TripShowResource($trip));
    }

    public function destroy(DeleteTripReservationRequest $request, int $order_id): JsonResponse
    {
        $order = $request->user()->orders()->findOrFail($order_id);

        try {
            TripReservationService::cancelOrder($order);

            return $this->success([
                'message' => 'Trip reservation cancelled successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error("Error cancelling trip reservation: " . $e->getMessage());
            return $this->failure('An error occurred while cancelling the reservation. Please try again later.', 500);
        }
    }
}