<?php

namespace App\Data\Properties;

final readonly class PropertySearchData
{
    public function __construct(
        public string $checkIn,
        public string $checkOut,
        public int $guests,
        public ?string $city,
    ) {}

    /**
     * @param array{
     *     check_in: string,
     *     check_out: string,
     *     guests: int,
     *     city?: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            checkIn: $data['check_in'],
            checkOut: $data['check_out'],
            guests: $data['guests'],
            city: $data['city'] ?? null,
        );
    }
}
