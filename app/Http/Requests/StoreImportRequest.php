<?php

namespace App\Http\Requests;

use App\Data\Imports\ImportData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportProperty',
    required: [
        'code',
        'name',
        'city',
    ],
    properties: [
        new OA\Property(
            property: 'code',
            type: 'string',
            example: 'BCN-0001',
            maxLength: 64,
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Apartment near Sagrada Familia',
            maxLength: 255,
        ),
        new OA\Property(
            property: 'city',
            type: 'string',
            example: 'Barcelona',
            maxLength: 100,
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

#[OA\Schema(
    schema: 'ImportOffer',
    required: [
        'external_id',
        'property',
        'check_in',
        'check_out',
        'max_guests',
        'price',
        'currency',
        'available_units',
        'expires_at',
    ],
    properties: [
        new OA\Property(
            property: 'external_id',
            type: 'string',
            example: 'offer-a-10001',
            maxLength: 128,
        ),
        new OA\Property(
            property: 'property',
            ref: '#/components/schemas/ImportProperty',
        ),
        new OA\Property(
            property: 'check_in',
            type: 'string',
            format: 'date',
            example: '2026-10-10',
        ),
        new OA\Property(
            property: 'check_out',
            type: 'string',
            format: 'date',
            example: '2026-10-15',
        ),
        new OA\Property(
            property: 'max_guests',
            type: 'integer',
            example: 4,
            maximum: 65535,
            minimum: 1,
        ),
        new OA\Property(
            property: 'price',
            type: 'integer',
            format: 'int64',
            example: 72500,
            minimum: 0,
        ),
        new OA\Property(
            property: 'currency',
            type: 'string',
            pattern: '^[A-Z]{3}$',
            example: 'EUR',
            maxLength: 3,
            minLength: 3,
        ),
        new OA\Property(
            property: 'available_units',
            type: 'integer',
            format: 'int64',
            example: 2,
            maximum: 4294967295,
            minimum: 0,
        ),
        new OA\Property(
            property: 'expires_at',
            type: 'string',
            format: 'date-time',
            example: '2026-09-10T23:59:59Z',
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

#[OA\Schema(
    schema: 'StoreImportRequest',
    required: [
        'supplier',
        'external_import_id',
        'sent_at',
        'offers',
    ],
    properties: [
        new OA\Property(
            property: 'supplier',
            type: 'string',
            example: 'supplier-a',
            maxLength: 64,
        ),
        new OA\Property(
            property: 'external_import_id',
            type: 'string',
            example: 'import-2026-09-01-001',
            maxLength: 128,
        ),
        new OA\Property(
            property: 'sent_at',
            type: 'string',
            format: 'date-time',
            example: '2026-09-01T10:00:00Z',
        ),
        new OA\Property(
            property: 'offers',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/ImportOffer',
            ),
        ),
    ],
    type: 'object',
    additionalProperties: false,
)]

class StoreImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier' => ['required', 'string', 'max:64', Rule::exists('suppliers', 'slug')],
            'external_import_id' => ['required', 'string', 'max:128'],
            'sent_at' => ['required', 'date'],
            'offers' => ['required', 'array'],
            'offers.*' => ['required', Rule::array(['external_id', 'property', 'check_in', 'check_out', 'max_guests', 'price', 'currency', 'available_units', 'expires_at'])],
            'offers.*.external_id' => ['required', 'string', 'max:128', 'distinct:strict'],
            'offers.*.property' => ['required', 'array:code,name,city'],
            'offers.*.property.code' => ['required', 'string', 'max:64'],
            'offers.*.property.name' => ['required', 'string', 'max:255'],
            'offers.*.property.city' => ['required', 'string', 'max:100'],
            'offers.*.check_in' => ['required', 'date_format:Y-m-d'],
            'offers.*.check_out' => ['required', 'date_format:Y-m-d', 'after:offers.*.check_in'],
            'offers.*.max_guests' => ['required', 'integer', 'min:1', 'max:65535'],
            'offers.*.price' => ['required', 'integer', 'min:0'],
            'offers.*.currency' => ['required', 'string', 'size:3', 'uppercase'],
            'offers.*.available_units' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'offers.*.expires_at' => ['required', 'date'],
        ];
    }

    public function toData(): ImportData
    {
        return ImportData::fromArray($this->validated());
    }
}
