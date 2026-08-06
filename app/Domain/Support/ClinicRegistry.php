<?php

namespace App\Domain\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for clinic (office) identity: ClinicNum -> display name.
 *
 * Multi-office readiness: the whole app filters by ClinicNum via MetricFilter already;
 * this registry removes the last single-office assumption — the hardcoded '8 Mile' label
 * scattered across controllers. When the `od_clinics` table is synced, names come straight
 * from OpenDental and new offices appear automatically with no code change. Until then it
 * falls back to the known single office.
 *
 * OpenDental convention: ClinicNum 0 means "unassigned / clinics-not-in-use", which for a
 * single-office practice IS the primary office — so 0 always maps to the primary name.
 */
class ClinicRegistry
{
    /** Primary office name used for ClinicNum 0 until od_clinics is synced. */
    private const PRIMARY_NAME = '8 Mile';

    /** @var array<int,string>|null lazily-built ClinicNum => name */
    private ?array $map = null;

    /** @return array<int,string> ClinicNum => display name */
    public function all(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $map = [0 => config('clinics.primary_name', self::PRIMARY_NAME)];

        if (Schema::hasTable('od_clinics')) {
            $rows = DB::table('od_clinics')
                ->get(['ClinicNum', 'Description', 'Abbr']);
            foreach ($rows as $r) {
                $name = trim((string) ($r->Description ?: $r->Abbr));
                if ($name !== '') {
                    $map[(int) $r->ClinicNum] = $name;
                }
            }
        }

        return $this->map = $map;
    }

    /** Display name for a clinic; falls back to "Location N" for unknown clinics. */
    public function name(int $clinicNum): string
    {
        return $this->all()[$clinicNum] ?? ('Location '.$clinicNum);
    }

    /** @return int[] all known ClinicNums */
    public function ids(): array
    {
        return array_keys($this->all());
    }

    public function exists(int $clinicNum): bool
    {
        return array_key_exists($clinicNum, $this->all());
    }

    /** True once real offices are configured (od_clinics synced with >1 clinic). */
    public function isMultiOffice(): bool
    {
        return count($this->all()) > 1;
    }
}
