<?php

namespace App\Domain\Support;

use App\Models\Office;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The single source of truth for clinic (office) identity: ClinicNum -> display name.
 *
 * Multi-office readiness: the whole app filters by ClinicNum via MetricFilter already;
 * this registry provides names for the active Office location.
 */
class ClinicRegistry
{
    /** @var array<int, array<int, string>> lazily-built officeId => (ClinicNum => name) */
    private array $maps = [];

    /** @return array<int,string> ClinicNum => display name for the given office */
    public function all(?int $officeId = null): array
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();

        if (isset($this->maps[$officeId])) {
            return $this->maps[$officeId];
        }

        $activeOffice = Office::find($officeId);
        $primaryName = $activeOffice?->name ?: config('clinics.primary_name', 'Main Office');

        $map = [0 => $primaryName];

        if (Schema::hasTable('od_clinics')) {
            $query = DB::table('od_clinics');
            if (Schema::hasColumn('od_clinics', 'office_id')) {
                $query->where('office_id', $officeId);
            }

            $rows = $query->get(['ClinicNum', 'Description', 'Abbr']);
            foreach ($rows as $r) {
                $name = trim((string) ($r->Description ?: $r->Abbr));
                if ($name !== '') {
                    $map[(int) $r->ClinicNum] = $name;
                }
            }
        }

        return $this->maps[$officeId] = $map;
    }

    /** Display name for a clinic; falls back to "Location N" for unknown clinics. */
    public function name(int $clinicNum, ?int $officeId = null): string
    {
        return $this->all($officeId)[$clinicNum] ?? ('Location '.$clinicNum);
    }

    /** @return int[] all known ClinicNums */
    public function ids(?int $officeId = null): array
    {
        return array_keys($this->all($officeId));
    }

    public function exists(int $clinicNum, ?int $officeId = null): bool
    {
        return array_key_exists($clinicNum, $this->all($officeId));
    }

    /** True once real offices are configured (od_clinics synced with >1 clinic). */
    public function isMultiOffice(?int $officeId = null): bool
    {
        return count($this->all($officeId)) > 1;
    }
}
