<?php

namespace App\Actions\Imports;

use App\Data\Imports\ImportData;
use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Supplier;

class AcceptImport
{
    public function handle(ImportData $data): Import
    {
        $supplier = $this->findSupplier($data);
        $import = $this->findOrCreateImport($data, $supplier);

        if ($import->wasRecentlyCreated) {
            ProcessImportJob::dispatch($import->id);
        }

        return $import;
    }

    private function findOrCreateImport(
        ImportData $data,
        Supplier $supplier,
    ): Import {
        $offers = $data->offersToArray();

        return Import::query()->createOrFirst(
            [
                'supplier_id' => $supplier->id,
                'external_import_id' => $data->externalImportId,
            ],
            [
                'sent_at' => $data->sentAt,
                'status' => ImportStatus::Pending,
                'payload' => $offers,
                'total_offers' => count($offers),
            ],
        );
    }

    private function findSupplier(ImportData $data): Supplier
    {
        return Supplier::query()
            ->where('slug', $data->supplierSlug)
            ->firstOrFail();
    }
}
