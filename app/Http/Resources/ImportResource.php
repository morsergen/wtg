<?php

namespace App\Http\Resources;

use App\Enums\ImportStatus;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Import */
#[OA\Schema(
    schema: 'ImportResource',
    required: ['id', 'status'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            format: 'int64',
            example: 15,
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            example: 'pending',
            enum: ImportStatus::class,
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

class ImportResource extends JsonResource
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
            'status' => $this->status->value,
        ];
    }
}
