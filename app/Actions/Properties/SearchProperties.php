<?php

namespace App\Actions\Properties;

use App\Data\Properties\PropertySearchData;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

final class SearchProperties
{
    /**
     * @return LengthAwarePaginator<int, Property>
     */
    public function handle(PropertySearchData $data): LengthAwarePaginator
    {
        return Property::query()
            ->whereHas('offers', function (Builder $query) use ($data): void {
                /** @var Builder<Offer> $query */
                $query->availableFor($data->checkIn, $data->checkOut, $data->guests);
            })
            ->withMin(
                [
                    'offers as best_offer_price' => function (Builder $query) use ($data): void {
                        /** @var Builder<Offer> $query */
                        $query->availableFor($data->checkIn, $data->checkOut, $data->guests);
                    },
                ],
                'price',
            )
            ->with([
                'city',
                'offers' => function (Relation $relation) use ($data): void {
                    /** @var HasMany<Offer, Property> $relation */
                    /** @var Builder<Offer> $query */
                    $query = $relation->getQuery();
                    $query->with('supplier')
                        ->availableFor($data->checkIn, $data->checkOut, $data->guests)
                        ->orderBy('price')
                        ->orderBy('id');

                    $relation->limit(1);
                },
            ])
            ->when(
                $data->city !== null,
                fn (Builder $query): Builder => $query->whereRelation('city', 'name', $data->city),
            )
            ->orderBy('best_offer_price')
            ->orderBy('properties.id')
            ->paginate(perPage: 15)
            ->withQueryString();
    }
}
