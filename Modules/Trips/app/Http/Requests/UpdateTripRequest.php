<?php

namespace Modules\Trips\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
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
            'bus_id' => ['sometimes', 'required', 'exists:buses,id'],
            'from_province_id' => ['sometimes', 'required', 'exists:provinces,id'],
            'to_province_id' => ['sometimes', 'required', 'exists:provinces,id'],
            'price_per_seat' => ['sometimes', 'required', 'numeric', 'min:0'],
            'trip_date' => ['sometimes', 'required', 'date', 'after_or_equal:today'],
            'departure_time' => ['sometimes', 'required', 'date_format:H:i'],
            'arrived_at' => ['sometimes', 'required', 'date_format:H:i', 'after:departure_time'],
        ];
    }
}
