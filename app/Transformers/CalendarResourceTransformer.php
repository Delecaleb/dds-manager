<?php

namespace App\Transformers;

use App\Models\OdOperatory;
use App\Models\Office;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * The Appointments Calendar's columns: one per operatory (chair) the selected location uses.
 *
 * - The location's own visible operatories (od_operatories), in OpenDental's ItemOrder.
 *   A clinic sees only its own chairs; a single-clinic office sees all of them.
 * - Plus every operatory an appointment in view is booked into, even if hidden, belonging
 *   to another clinic, or not synced yet — FullCalendar silently drops an event whose
 *   column does not exist, so no appointment may ever be left without one.
 * - Appointments with no operatory get an "Unassigned" column.
 */
class CalendarResourceTransformer
{
    /** Columns sort by `order`: synced chairs by position, then unsynced chairs by number, then Unassigned. */
    private const UNSYNCED_ORDER = 1_000_000;

    private const UNASSIGNED_ORDER = 2_000_000_000;

    public static function transform(Collection $appointments, bool $activeOnly = false, int|string|null $clinicId = null, ?int $officeId = null): array
    {
        $officeId ??= Office::getActiveOfficeId();
        $clinic = ($clinicId !== null && $clinicId !== '' && $clinicId !== 'all') ? (int) $clinicId : null;

        $usedOps = $appointments->map(fn ($apt) => (int) ($apt->Op ?? 0))->unique()->values();
        $bookedOps = $usedOps->filter(fn (int $op) => $op > 0)->values()->all();

        $operatories = Schema::hasTable('od_operatories')
            ? OdOperatory::withoutGlobalScopes()
                ->where('office_id', $officeId)
                ->where(function ($query) use ($clinic, $bookedOps) {
                    $query->where(function ($own) use ($clinic) {
                        $own->visible();
                        if ($clinic !== null) {
                            $own->where('ClinicNum', $clinic);
                        }
                    });
                    if ($bookedOps !== []) {
                        $query->orWhereIn('OperatoryNum', $bookedOps);
                    }
                })
                ->orderBy('ItemOrder')
                ->orderBy('OperatoryNum')
                ->get()
            : collect();

        $resources = [];
        foreach ($operatories as $position => $operatory) {
            $resources[(int) $operatory->OperatoryNum] = self::column((int) $operatory->OperatoryNum, $operatory->label(), $position);
        }

        // Booked into an operatory we have no row for (not synced yet): still give it a column.
        foreach ($bookedOps as $op) {
            $resources[$op] ??= self::column($op, 'Op '.$op, self::UNSYNCED_ORDER + $op);
        }

        if ($usedOps->contains(0)) {
            $resources[0] = self::column(0, 'Unassigned', self::UNASSIGNED_ORDER);
        }

        if ($activeOnly) {
            $active = array_intersect_key($resources, array_flip($usedOps->all()));

            // Nothing booked: show the location's chairs rather than an empty grid.
            if ($active !== []) {
                $resources = $active;
            }
        }

        return array_values($resources);
    }

    /** @return array{id: string, title: string, op_num: string, order: int} */
    private static function column(int $op, string $title, int $order): array
    {
        return ['id' => 'op-'.$op, 'title' => $title, 'op_num' => (string) $op, 'order' => $order];
    }
}
