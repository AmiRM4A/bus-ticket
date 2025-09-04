<?php

namespace Modules\Trips\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTripReservationRequest extends FormRequest
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
            'seat_ids' => ['sometimes', 'array'],
            'seat_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
