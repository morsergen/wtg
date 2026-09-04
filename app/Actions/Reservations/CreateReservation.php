<?php

namespace App\Actions\Reservations;

use App\Data\Reservations\ReservationData;
use App\Exceptions\OfferUnavailableException;
use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

final class CreateReservation
{
    /**
     * @throws \Throwable
     */
    public function handle(
        Offer $offer,
        ReservationData $data,
    ): Reservation {
        return DB::transaction(function () use ($offer, $data): Reservation {
            $lockedOffer = Offer::query()
                ->lockForUpdate()
                ->findOrFail($offer->getKey());

            if (
                $lockedOffer->available_units <= 0
                || $lockedOffer->expires_at->lessThanOrEqualTo(now())
            ) {
                throw new OfferUnavailableException;
            }

            $lockedOffer->decrement('available_units');

            return $lockedOffer->reservations()->create([
                'client_reference' => $data->clientReference,
                'customer_name' => $data->customerName,
                'customer_email' => $data->customerEmail,
            ]);
        });
    }
}
