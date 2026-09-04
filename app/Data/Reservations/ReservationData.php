<?php

namespace App\Data\Reservations;

final readonly class ReservationData
{
    public function __construct(
        public string $clientReference,
        public string $customerName,
        public string $customerEmail,
    ) {}

    /**
     * @param array{
     *     client_reference: string,
     *     customer_name: string,
     *     customer_email: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientReference: $data['client_reference'],
            customerName: $data['customer_name'],
            customerEmail: $data['customer_email'],
        );
    }
}
