<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Property */
#[OA\Schema(
    schema: 'PropertyResource',
    required: [
        'code',
        'name',
        'city',
        'best_offer',
    ],
    properties: [
        new OA\Property(
            property: 'code',
            type: 'string',
            example: 'BCN-0001',
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Apartment near Sagrada Familia',
        ),
        new OA\Property(
            property: 'city',
            type: 'string',
            example: 'Barcelona',
        ),
        new OA\Property(
            property: 'best_offer',
            ref: '#/components/schemas/BestOfferResource',
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'city' => $this->city->name,
            'best_offer' => new BestOfferResource($this->offers->first()),
        ];
    }
}
