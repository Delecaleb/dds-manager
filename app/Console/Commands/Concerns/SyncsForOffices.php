<?php

namespace App\Console\Commands\Concerns;

use App\Models\Office;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

trait SyncsForOffices
{
    /**
     * Get the offices to process based on the --office-id option.
     *
     * @return Collection<int, Office>
     */
    protected function getTargetOffices(): Collection
    {
        $officeId = $this->hasOption('office-id') ? $this->option('office-id') : null;

        if ($officeId !== null) {
            return Office::where('id', (int) $officeId)->get();
        }

        $activeOffices = Office::where('is_active', true)->get();

        if ($activeOffices->isEmpty()) {
            $first = Office::first();

            return $first ? new Collection([$first]) : new Collection;
        }

        return $activeOffices;
    }

    /**
     * Run a sync callback across all target offices with fault isolation.
     *
     * If an individual office fails (e.g. network timeout or invalid credentials),
     * the error is logged, and the loop continues to the next office.
     *
     * @param  (callable(Office): void)  $callback
     */
    protected function syncEachOffice(string $resourceName, callable $callback): int
    {
        DB::disableQueryLog();

        $offices = $this->getTargetOffices();

        if ($offices->isEmpty()) {
            $this->warn("No offices found to sync {$resourceName}.");

            return Command::SUCCESS;
        }

        $failedOffices = [];

        foreach ($offices as $office) {
            $this->info("Syncing {$resourceName} for office [{$office->id}] {$office->name}...");

            try {
                $callback($office);
            } catch (Throwable $e) {
                $failedOffices[] = "Office #{$office->id} ({$office->name})";
                $message = "Failed syncing {$resourceName} for office [{$office->id}] {$office->name}: ".$e->getMessage();
                $this->error($message);
                Log::error($message, [
                    'office_id' => $office->id,
                    'office_name' => $office->name,
                    'resource' => $resourceName,
                    'exception' => $e,
                ]);
            }
        }

        if (! empty($failedOffices)) {
            $this->info("Sync {$resourceName} finished with errors in: ".implode(', ', $failedOffices));
        } else {
            $this->info("Successfully completed {$resourceName} sync for all target offices.");
        }

        return Command::SUCCESS;
    }
}
