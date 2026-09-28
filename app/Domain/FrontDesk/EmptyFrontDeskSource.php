<?php

namespace App\Domain\FrontDesk;

use App\Domain\Support\Location;

/**
 * The source until a call platform is connected: every page renders its full layout with
 * real offices (from ClinicRegistry) and empty states where call data will appear.
 */
class EmptyFrontDeskSource extends BaseFrontDeskSource
{
    public function isLive(): bool
    {
        return false;
    }

    public function label(): string
    {
        return 'No call platform connected';
    }

    protected function values(string $key, array $labels, FrontDeskFilter $filter): array
    {
        return [];
    }

    protected function summary(FrontDeskFilter $filter): array
    {
        return [
            'offices_live' => 0,
            'offices_total' => count($this->offices($filter)),
            'calls' => null, 'calls_change' => null,
            'booked' => null, 'booked_new' => null, 'booked_existing' => null, 'booked_change' => null,
            'production' => null, 'production_change' => null,
            'success_rate' => null, 'success_change' => null,
            'transfer_rate' => null, 'transfer_change' => null,
            'close_rate' => null,
            'time_saved' => null, 'time_saved_change' => null, 'avg_minutes' => null,
            'unsuccessful' => null, 'missed_np' => null,
        ];
    }

    protected function ranked(string $key, array $labels, FrontDeskFilter $filter): array
    {
        return [];
    }

    protected function officeTotals(Location $location, FrontDeskFilter $filter): array
    {
        return ['status' => 'not_connected', 'calls' => null, 'appts' => null, 'production' => null];
    }

    public function calls(Location $location): array
    {
        return [];
    }

    public function call(Location $location, string $id): ?array
    {
        return null;
    }

    public function conversations(Location $location): array
    {
        return [];
    }

    public function conversation(Location $location, string $id): ?array
    {
        return null;
    }

    public function messageActions(Location $location): array
    {
        return [];
    }

    public function bookings(Location $location): array
    {
        return [];
    }

    public function booking(Location $location, string $id): ?array
    {
        return null;
    }

    public function workflows(Location $location): array
    {
        return [];
    }

    public function schedule(Location $location, string $date): array
    {
        return ['columns' => [], 'appointments' => [], 'appointmentTypes' => []];
    }

    public function settings(Location $location): array
    {
        $blankWeek = array_fill_keys(
            ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            null,
        );

        return [
            'general' => [
                'location_name' => $location->name,
                'assistant_name' => null,
                'address' => null,
                'timezone' => config('app.timezone'),
                'phone' => null,
                'email' => null,
                'services' => [],
            ],
            'hours' => ['closures' => [], 'business' => $blankWeek, 'admin' => $blankWeek],
            'ai-actions' => ['new' => [], 'existing' => [], 'actions' => []],
            'knowledge-base' => ['content' => ''],
            'payment-options' => ['packages' => [], 'insurance' => [], 'financing' => ''],
            'notifications' => ['recipients' => [], 'types' => ['Call Completed (recommended)', 'SMS: Assistant Needed']],
            'online-scheduling' => [
                'link' => null,
                'redirect' => null,
                'services' => ['new' => [], 'existing' => []],
                'recipients' => [],
            ],
            'phone-numbers' => ['agent' => [], 'transfer' => []],
            'team' => ['members' => []],
        ];
    }
}
