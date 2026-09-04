<?php

namespace App\Data\Imports;

final readonly class OfferData
{
    public function __construct(
        public string $externalId,
        public PropertyData $property,
        public string $checkIn,
        public string $checkOut,
        public int $maxGuests,
        public int $price,
        public string $currency,
        public int $availableUnits,
        public string $expiresAt,
    ) {}

    /**
     * @param array{
     *     external_id: string,
     *     property: array{code: string, name: string, city: string},
     *     check_in: string,
     *     check_out: string,
     *     max_guests: int,
     *     price: int,
     *     currency: string,
     *     available_units: int,
     *     expires_at: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            externalId: $data['external_id'],
            property: PropertyData::fromArray($data['property']),
            checkIn: $data['check_in'],
            checkOut: $data['check_out'],
            maxGuests: $data['max_guests'],
            price: $data['price'],
            currency: $data['currency'],
            availableUnits: $data['available_units'],
            expiresAt: $data['expires_at'],
        );
    }

    /**
     * @return array{
     *     external_id: string,
     *     property: array{code: string, name: string, city: string},
     *     check_in: string,
     *     check_out: string,
     *     max_guests: int,
     *     price: int,
     *     currency: string,
     *     available_units: int,
     *     expires_at: string
     * }
     */
    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'property' => $this->property->toArray(),
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'max_guests' => $this->maxGuests,
            'price' => $this->price,
            'currency' => $this->currency,
            'available_units' => $this->availableUnits,
            'expires_at' => $this->expiresAt,
        ];
    }
}
