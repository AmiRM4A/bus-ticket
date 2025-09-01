<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidOrderException;
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
    public function __construct(
        private readonly TripReservationService $tripReservationService
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $query = Trip::with([
            'origin:id,name',
            'destination:id,name',
            'bus:id,model,plate,seats_count',
        ]);
        $query->when($request->query('bus_id'), fn ($q) => $q->where('bus_id', $request->get('bus_id')))
            ->when($request->query('from_province_id'), fn ($q) => $q->where('from_province_id', $request->get('from_province_id')))
            ->when($request->query('to_province_id'), fn ($q) => $q->where('to_province_id', $request->get('to_province_id')))
            ->when($request->query('min_price'), fn ($q) => $q->where('price_per_seat', '>=', $request->get('min_price')))
            ->when($request->query('max_price'), fn ($q) => $q->where('price_per_seat', '<=', $request->get('max_price')));

        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);
        $trips = $query->paginate($perPage, page: $page);

        return $this->success(TripIndexResource::collection($trips)->response()->getData());
    }

    public function store(StoreTripReservationRequest $request): JsonResponse
    {
        try {
            $trip = Trip::findOrFail($request->trip_id);
            $user = $request->user();
            $reservationData = $request->validated('passengers');

            $order = $this->tripReservationService->createReservation($user, $trip, $reservationData);

            return $this->success([
                'order_id' => $order->id,
            ], HttpResponse::HTTP_CREATED);
        } catch (InvalidSeatForReservation $e) {
            return $this->failure($e->getMessage(), HttpResponse::HTTP_BAD_REQUEST);
        } catch (Throwable $th) {
            Log::error(__('api.unexpected_reservation_error'), [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            return $this->failure(__('api.reservation_failed'));
        }
    }

    public function show(Trip $trip): JsonResponse
    {
        return $this->success(new TripShowResource($trip));
    }

    public function destroy(DeleteTripReservationRequest $request, int $order_id): JsonResponse
    {
        // Fetching order_id for the auth user (avoid selecting other user's order)
        $order = Order::forUser(auth()->id())
            ->pending() // Only Pending order (we don't cancel "Cancelled" or "Completed" orders)
            ->findOrFail($order_id);
        $seatsToCancel = $request->validated('seat_ids');

        try {
            $this->tripReservationService->cancelReservation($order, $seatsToCancel);

            return $this->success();
        } catch (InvalidOrderException $e) {
            return $this->failure(message: $e->getMessage(), status: HttpResponse::HTTP_NOT_FOUND);
        } catch (Throwable $th) {
            Log::error(__('api.error_cancelling_reservation'), [
                'message' => $th->getMessage(),
                'order_id' => $order->id,
                'seats_to_cancel' => $seatsToCancel,
            ]);

            return $this->failure(__('api.cancellation_failed'));
        }
    }

    public function update(UpdateTripRequest $request, Trip $trip): JsonResponse
    {
        $trip->update($request->validated());

        return $this->success(new TripShowResource($trip));
    }
}
