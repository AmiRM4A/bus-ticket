<?php

namespace Modules\Trips\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'integer'],

            'passengers' => ['required', 'array', 'min:1'],
            // By moving this check to service logic we can avoid extra validation queries.
            'passengers.*.trip_seat_id' => ['required', 'int', 'distinct', 'exists:trip_seats,id'],
            'passengers.*.first_name' => ['required', 'string'],
            'passengers.*.last_name' => ['required', 'string'],
            'passengers.*.mobile' => ['required', 'string'],
            'passengers.*.national_code' => ['required', 'string'],
            'passengers.*.birth_date' => ['nullable', 'date'],
            'passengers.*.gender' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
