<?php

namespace App\Actions\Imports;

use App\Data\Imports\OfferData;
use App\Enums\ImportStatus;
use App\Models\City;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessImport
{
    /**
     * @throws Throwable
     */
    public function handle(int $importId): void
    {
        $import = Import::query()->findOrFail($importId);

        if ($import->status === ImportStatus::Completed) {
            return;
        }

        $this->markAsProcessing($import);

        DB::transaction(function () use ($import): void {
            $this->processOffers($import);
            $this->markAsCompleted($import);
        });
    }

    private function markAsProcessing(Import $import): void
    {
        $import->update([
            'status' => ImportStatus::Processing,
            'error' => null,
            'completed_at' => null,
            'total_offers' => count($import->payload),
            'processed_offers' => 0,
        ]);
    }

    private function markAsCompleted(Import $import): void
    {
        $import->update([
            'status' => ImportStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    private function processOffers(Import $import): void
    {
        foreach ($import->payload as $offerPayload) {
            $offerData = OfferData::fromArray($offerPayload);
            $this->processOffer($import, $offerData);
            $import->increment('processed_offers');
        }
    }

    private function processOffer(Import $import, OfferData $offerData): void
    {
        $city = City::query()->firstOrCreate([
            'name' => $offerData->property->city,
        ]);

        $property = Property::query()->updateOrCreate(
            ['code' => $offerData->property->code],
            [
                'city_id' => $city->id,
                'name' => $offerData->property->name,
            ],
        );

        Offer::query()->updateOrCreate(
            [
                'supplier_id' => $import->supplier_id,
                'external_id' => $offerData->externalId,
            ],
            [
                'property_id' => $property->id,
                'import_id' => $import->id,
                'check_in' => $offerData->checkIn,
                'check_out' => $offerData->checkOut,
                'max_guests' => $offerData->maxGuests,
                'price' => $offerData->price,
                'currency' => $offerData->currency,
                'available_units' => $offerData->availableUnits,
                'expires_at' => $offerData->expiresAt,
            ],
        );
    }
}
