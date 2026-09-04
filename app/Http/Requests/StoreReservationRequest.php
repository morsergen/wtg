<?php

namespace App\Http\Requests;

use App\Data\Reservations\ReservationData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreReservationRequest',
    required: [
        'client_reference',
        'customer_name',
        'customer_email',
    ],
    properties: [
        new OA\Property(
            property: 'client_reference',
            type: 'string',
            example: 'web-order-9f782b1c',
            maxLength: 128,
        ),
        new OA\Property(
            property: 'customer_name',
            type: 'string',
            example: 'John Smith',
            maxLength: 255,
        ),
        new OA\Property(
            property: 'customer_email',
            type: 'string',
            format: 'email',
            example: 'john@example.com',
            maxLength: 255,
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

class StoreReservationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_reference' => ['required', 'string', 'max:128'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
        ];
    }

    public function toData(): ReservationData
    {
        return ReservationData::fromArray($this->validated());
    }
}
