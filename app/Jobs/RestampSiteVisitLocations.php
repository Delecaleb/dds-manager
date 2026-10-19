<?php

namespace App\Jobs;

use App\Domain\Marketing\VisitLocationResolver;
use App\Models\MarketingSite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Re-credit a tracked site's past visits after its locations, codes or paths changed. Queued
 * because a busy site has many visits; unique per site so repeated saves run it once.
 */
class RestampSiteVisitLocations implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 600;

    public function __construct(public readonly int $siteId)
    {
        // The scheduler's workers drain only the sync queues (routes/console.php); the
        // priority one runs it between sync jobs, so a saved change shows within minutes.
        $this->onConnection(config('sync.queue.connection'));
        $this->onQueue(config('sync.queue.priority_name'));
    }

    public function uniqueId(): string
    {
        return (string) $this->siteId;
    }

    public function handle(VisitLocationResolver $locations): void
    {
        $site = MarketingSite::find($this->siteId);

        if ($site !== null) {
            $locations->restamp($site);
        }
    }
}
