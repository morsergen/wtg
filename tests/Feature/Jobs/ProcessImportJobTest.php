<?php

namespace Tests\Feature\Jobs;

use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
use Throwable;

class ProcessImportJobTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @throws Throwable
     */
    public function test_empty_payload_completes_import_with_zero_counters(): void
    {
        $import = Import::factory()->create([
            'payload' => [],
        ]);

        $this->app->call([
            new ProcessImportJob($import->id),
            'handle',
        ]);

        $import->refresh();

        $this->assertSame(ImportStatus::Completed, $import->status);
        $this->assertSame(0, $import->total_offers);
        $this->assertSame(0, $import->processed_offers);
        $this->assertNotNull($import->completed_at);
    }

    /**
     * @throws Throwable
     */
    public function test_valid_payload_creates_city_property_and_offer(): void
    {
        $import = Import::factory()->create([
            'payload' => [$this->offerPayload()],
        ]);

        $this->app->call([
            new ProcessImportJob($import->id),
            'handle',
        ]);

        $import->refresh();

        $this->assertSame(ImportStatus::Completed, $import->status);
        $this->assertSame(1, $import->total_offers);
        $this->assertSame(1, $import->processed_offers);

        $this->assertDatabaseHas('cities', [
            'name' => 'Barcelona',
        ]);

        $this->assertDatabaseHas('properties', [
            'code' => 'BCN-0001',
            'name' => 'Apartment near Sagrada Familia',
        ]);

        $this->assertDatabaseHas('offers', [
            'import_id' => $import->id,
            'supplier_id' => $import->supplier_id,
            'external_id' => 'offer-a-10001',
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 72_500,
            'currency' => 'EUR',
            'available_units' => 2,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_new_import_updates_existing_offer_with_same_supplier_and_external_id(): void
    {
        $supplier = Supplier::factory()->create();

        $firstImport = Import::factory()
            ->for($supplier)
            ->create([
                'payload' => [$this->offerPayload()],
            ]);

        $this->app->call([
            new ProcessImportJob($firstImport->id),
            'handle',
        ]);

        $updatedOffer = array_replace(
            $this->offerPayload(),
            [
                'price' => 65_000,
                'available_units' => 1,
            ],
        );

        $secondImport = Import::factory()
            ->for($supplier)
            ->create([
                'payload' => [$updatedOffer],
            ]);

        $this->app->call([
            new ProcessImportJob($secondImport->id),
            'handle',
        ]);

        $this->assertDatabaseCount('offers', 1);

        $this->assertDatabaseHas('offers', [
            'supplier_id' => $supplier->id,
            'import_id' => $secondImport->id,
            'external_id' => 'offer-a-10001',
            'price' => 65_000,
            'available_units' => 1,
        ]);
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
    private function offerPayload(): array
    {
        return [
            'external_id' => 'offer-a-10001',
            'property' => [
                'code' => 'BCN-0001',
                'name' => 'Apartment near Sagrada Familia',
                'city' => 'Barcelona',
            ],
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 72_500,
            'currency' => 'EUR',
            'available_units' => 2,
            'expires_at' => '2026-09-10T23:59:59Z',
        ];
    }
}
