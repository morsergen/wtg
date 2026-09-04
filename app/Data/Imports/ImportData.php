<?php

namespace App\Data\Imports;

final readonly class ImportData
{
    /**
     * @param  list<OfferData>  $offers
     */
    public function __construct(
        public string $supplierSlug,
        public string $externalImportId,
        public string $sentAt,
        public array $offers,
    ) {}

    /**
     * @param array{
     *     supplier: string,
     *     external_import_id: string,
     *     sent_at: string,
     *     offers: list<array{
     *         external_id: string,
     *         property: array{code: string, name: string, city: string},
     *         check_in: string,
     *         check_out: string,
     *         max_guests: int,
     *         price: int,
     *         currency: string,
     *         available_units: int,
     *         expires_at: string
     *     }>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            supplierSlug: $data['supplier'],
            externalImportId: $data['external_import_id'],
            sentAt: $data['sent_at'],
            offers: array_map(
                static fn (array $offer): OfferData => OfferData::fromArray($offer),
                $data['offers'],
            ),
        );
    }

    /**
     * @return list<array{
     *     external_id: string,
     *     property: array{code: string, name: string, city: string},
     *     check_in: string,
     *     check_out: string,
     *     max_guests: int,
     *     price: int,
     *     currency: string,
     *     available_units: int,
     *     expires_at: string
     * }>
     */
    public function offersToArray(): array
    {
        return array_map(
            static fn (OfferData $offer): array => $offer->toArray(),
            $this->offers,
        );
    }
}
