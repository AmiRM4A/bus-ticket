<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.trip_seat_id' => ['required', 'int'],
            'passengers.*.first_name' => ['required', 'string'],
            'passengers.*.last_name' => ['required', 'string'],
            'passengers.*.mobile' => ['required', 'string'],
            'passengers.*.national_code' => ['required', 'string'],
            'passengers.*.birth_date' => ['nullable', 'date'],
        ];
    }
}
