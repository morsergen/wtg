<?php

namespace App\Jobs;

use App\Actions\Imports\ProcessImport;
use App\Enums\ImportStatus;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $importId) {}

    /**
     * Execute the job.
     *
     * @throws Throwable
     */
    public function handle(ProcessImport $processImport): void
    {
        $processImport->handle($this->importId);
    }

    public function failed(?Throwable $exception): void
    {
        $import = Import::query()->find($this->importId);

        if ($import === null) {
            return;
        }

        $import->update([
            'status' => ImportStatus::Failed,
            'error' => $exception?->getMessage(),
            'completed_at' => null,
        ]);
    }
}
