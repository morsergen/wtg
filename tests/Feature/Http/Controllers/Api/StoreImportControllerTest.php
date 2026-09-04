<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Jobs\ProcessImportJob;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreImportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_accepts_import_and_dispatches_processing_job(): void
    {
        Queue::fake([ProcessImportJob::class]);

        $supplier = Supplier::factory()->create();

        $response = $this->postJson(
            route('imports.store'),
            $this->validPayload($supplier),
        );

        $response
            ->assertAccepted()
            ->assertJsonPath('data.status', 'pending');

        $importId = $response->json('data.id');

        $this->assertDatabaseHas('imports', [
            'id' => $importId,
            'supplier_id' => $supplier->id,
            'external_import_id' => 'import-001',
            'status' => 'pending',
            'total_offers' => 1,
        ]);

        Queue::assertPushed(
            ProcessImportJob::class,
            fn (ProcessImportJob $job): bool => $job->importId === $importId,
        );
    }

    public function test_it_does_not_duplicate_import_with_the_same_supplier_and_external_id(): void
    {
        Queue::fake([ProcessImportJob::class]);

        $supplier = Supplier::factory()->create();
        $payload = $this->validPayload($supplier);

        $firstResponse = $this->postJson(route('imports.store'), $payload);
        $secondResponse = $this->postJson(route('imports.store'), $payload);

        $firstResponse->assertAccepted();
        $secondResponse->assertAccepted();

        $this->assertSame(
            $firstResponse->json('data.id'),
            $secondResponse->json('data.id'),
        );

        $this->assertDatabaseCount('imports', 1);

        Queue::assertPushed(ProcessImportJob::class, 1);
    }

    public function test_it_rejects_an_unknown_supplier(): void
    {
        Queue::fake([ProcessImportJob::class]);

        $supplier = Supplier::factory()->make([
            'slug' => 'unknown-supplier',
        ]);

        $response = $this->postJson(
            route('imports.store'),
            $this->validPayload($supplier),
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['supplier']);

        $this->assertDatabaseCount('imports', 0);

        Queue::assertNotPushed(ProcessImportJob::class);
    }

    public function test_it_rejects_import_without_external_import_id(): void
    {
        Queue::fake([ProcessImportJob::class]);

        $supplier = Supplier::factory()->create();
        $payload = $this->validPayload($supplier);

        unset($payload['external_import_id']);

        $response = $this->postJson(route('imports.store'), $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['external_import_id']);

        $this->assertDatabaseCount('imports', 0);

        Queue::assertNotPushed(ProcessImportJob::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(Supplier $supplier): array
    {
        return [
            'supplier' => $supplier->slug,
            'external_import_id' => 'import-001',
            'sent_at' => '2026-09-04T10:00:00Z',
            'offers' => [
                [
                    'external_id' => 'offer-001',
                    'property' => [
                        'code' => 'property-001',
                        'name' => 'Test Hotel',
                        'city' => 'Kyiv',
                    ],
                    'check_in' => '2026-10-10',
                    'check_out' => '2026-10-15',
                    'max_guests' => 2,
                    'price' => 50_000,
                    'currency' => 'USD',
                    'available_units' => 3,
                    'expires_at' => '2026-10-01T12:00:00Z',
                ],
            ],
        ];
    }
}
