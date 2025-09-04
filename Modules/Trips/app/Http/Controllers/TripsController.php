<?php

namespace Modules\Trips\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Modules\Orders\Http\Resources\OrderResource;
use Modules\Trips\Exceptions\InvalidSeatForReservation;
use Modules\Trips\Http\Requests\StoreTripReservationRequest;
use Modules\Trips\Http\Requests\UpdateTripRequest;
use Modules\Trips\Http\Resources\TripIndexResource;
use Modules\Trips\Http\Resources\TripShowResource;
use Modules\Trips\Models\Trip;
use Modules\Trips\Services\TripReservationService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class TripsController extends ApiController
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
        $trips = $query->orderBy('trip_date')
            ->orderBy('departure_time')
            ->paginate($perPage, page: $page);

        return $this->success(TripIndexResource::collection($trips)->response()->getData());
    }

    public function store(StoreTripReservationRequest $request): JsonResponse
    {
        try {
            $trip = Trip::findOrFail($request->trip_id);
            if (! $trip->hasRemainingSeats()) {
                return $this->failure(message: __('api.trip_does_not_have_remaining_seats'), status: HttpResponse::HTTP_CONFLICT);
            }

            $user = $request->user();
            $reservationData = $request->validated('passengers');

            $order = $this->tripReservationService->createReservation($user, $trip, $reservationData);

            // An event could get dispatched here
            // e.g => for sending sms to passengers or...

            return $this->success(new OrderResource($order), status: HttpResponse::HTTP_CREATED);
        } catch (InvalidSeatForReservation $e) {
            return $this->failure(message: $e->getMessage(), status: HttpResponse::HTTP_BAD_REQUEST);
        } catch (Throwable $th) {
            Log::error(__('api.unexpected_reservation_error'), [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            return $this->failure(message: __('api.reservation_failed'));
        }
    }

    public function show(Trip $trip): JsonResponse
    {
        $trip->load([
            'bus:id,model,plate,seats_count',
            'seats:id,trip_id,status,reserved_gender,bus_seat_id',
            'seats.busSeat:id,row,column,name',
            'origin:id,name',
            'destination:id,name',
        ]);

        return $this->success(new TripShowResource($trip));
    }

    public function update(UpdateTripRequest $request, Trip $trip): JsonResponse
    {
        $trip->update($request->validated());

        return $this->success(new TripShowResource($trip));
    }
}
