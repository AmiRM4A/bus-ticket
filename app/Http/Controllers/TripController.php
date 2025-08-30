<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidSeatForReservation;
use App\Http\Requests\DeleteTripReservationRequest;
use App\Http\Requests\StoreTripReservationRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripIndexResource;
use App\Http\Resources\TripShowResource;
use App\Models\Order;
use App\Models\Trip;
use App\Services\TripReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class TripController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);
        $trips = Trip::with([
            'origin:id,name',
            'destination:id,name',
            'bus:id,model,plate,seats_count',
        ])->paginate($perPage, page: $page);

        return $this->success(TripIndexResource::collection($trips)->response()->getData());
    }

    public function store(StoreTripReservationRequest $request): JsonResponse
    {
        try {
            $trip = Trip::findOrFail($request->trip_id);
            $user = $request->user();
            $reservationData = $request->validated('passengers');

            $order = TripReservationService::createReservation($user, $trip, $reservationData);

            return $this->success([
                'order_id' => $order->id,
            ], HttpResponse::HTTP_CREATED);
        } catch (InvalidSeatForReservation $e) {
            return $this->failure($e->getMessage(), HttpResponse::HTTP_BAD_REQUEST);
        } catch (Throwable $th) {
            dd($th->getMessage(), $th->getFile(), $th->getLine());
            Log::error('Unexpected error during trip reservation: '.$th->getMessage());

            return $this->failure('An unexpected error occurred during reservation.');
        }
    }

    public function show(Trip $trip): JsonResponse
    {
        return $this->success(new TripShowResource($trip));
    }

    public function destroy(DeleteTripReservationRequest $request, int $order_id): JsonResponse
    {
        // Fetching order_id for the auth user (avoid selecting other user's order)
        $order = Order::pending()
            ->whereUserId($request->user()->id)
            ->findOrFail($order_id);

        try {
            TripReservationService::cancelReservation($order);

            return $this->success();
        } catch (Throwable $e) {
            Log::error('Error cancelling trip reservation: '.$e->getMessage());

            return $this->failure('An error occurred while cancelling the reservation.');
        }
    }

    public function update(UpdateTripRequest $request, Trip $trip): JsonResponse
    {
        $trip->update($request->validated());

        return $this->success(new TripShowResource($trip));
    }
}
