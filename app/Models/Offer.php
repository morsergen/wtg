<?php

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $expires_at
 * @property-read Supplier $supplier
 */
#[Fillable([
    'supplier_id',
    'property_id',
    'import_id',
    'external_id',
    'check_in',
    'check_out',
    'max_guests',
    'price',
    'currency',
    'available_units',
    'expires_at',
])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'max_guests' => 'integer',
            'price' => 'integer',
            'available_units' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<Import, $this>
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @param  Builder<Offer>  $query
     */
    #[Scope]
    protected function availableFor(
        Builder $query,
        string $checkIn,
        string $checkOut,
        int $guests,
    ): void {
        $query
            ->where('check_in', $checkIn)
            ->where('check_out', $checkOut)
            ->where('max_guests', '>=', $guests)
            ->where('available_units', '>', 0)
            ->where('expires_at', '>', now());
    }
}
