<?php

namespace App\Http\Resources;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Reservation */
#[OA\Schema(
    schema: 'ReservationResource',
    required: [
        'id',
        'offer_id',
        'client_reference',
        'customer_name',
        'customer_email',
        'created_at',
    ],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            format: 'int64',
            example: 81,
        ),
        new OA\Property(
            property: 'offer_id',
            type: 'integer',
            format: 'int64',
            example: 125,
        ),
        new OA\Property(
            property: 'client_reference',
            type: 'string',
            example: 'web-order-9f782b1c',
        ),
        new OA\Property(
            property: 'customer_name',
            type: 'string',
            example: 'John Smith',
        ),
        new OA\Property(
            property: 'customer_email',
            type: 'string',
            format: 'email',
            example: 'john@example.com',
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2026-09-04T12:00:00Z',
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'offer_id' => $this->offer_id,
            'client_reference' => $this->client_reference,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'created_at' => $this->created_at->toIso8601ZuluString(),
        ];
    }
}
