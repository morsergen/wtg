<?php

namespace App\Http\Resources;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Offer */
#[OA\Schema(
    schema: 'BestOfferResource',
    required: [
        'id',
        'supplier',
        'price',
        'currency',
        'available_units',
        'expires_at',
    ],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            format: 'int64',
            example: 42,
        ),
        new OA\Property(
            property: 'supplier',
            type: 'string',
            example: 'supplier-a',
        ),
        new OA\Property(
            property: 'price',
            type: 'integer',
            format: 'int64',
            example: 65_000,
        ),
        new OA\Property(
            property: 'currency',
            type: 'string',
            example: 'EUR',
        ),
        new OA\Property(
            property: 'available_units',
            type: 'integer',
            example: 2,
        ),
        new OA\Property(
            property: 'expires_at',
            type: 'string',
            format: 'date-time',
            example: '2026-10-09T23:59:59Z',
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

class BestOfferResource extends JsonResource
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
            'supplier' => $this->supplier->slug,
            'price' => $this->price,
            'currency' => $this->currency,
            'available_units' => $this->available_units,
            'expires_at' => $this->expires_at->toIso8601ZuluString(),
        ];
    }
}
