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

        $map = [];

        if (Schema::hasTable('od_clinics')) {
            $query = DB::table('od_clinics');
            if (Schema::hasColumn('od_clinics', 'office_id')) {
                $query->where('office_id', $officeId);
            }

            $rows = $query->orderBy('ItemOrder')->orderBy('ClinicNum')->get(['ClinicNum', 'Description', 'Abbr']);
            foreach ($rows as $r) {
                $name = trim((string) ($r->Description ?: $r->Abbr));
                if ($name !== '') {
                    $map[(int) $r->ClinicNum] = $name;
                }
            }
        }

        if (empty($map)) {
            $map = [0 => $primaryName];
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

    public function hasClinics(?int $officeId = null): bool
    {
        return $this->isMultiOffice($officeId);
    }

    /**
     * Get the active clinic number for the specified office.
     * Persisted per office in session('active_clinic_id_{officeId}').
     */
    public function getActiveClinicNum(?int $officeId = null): ?int
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        if (! $officeId) {
            return null;
        }

        $sessionKey = "active_clinic_id_{$officeId}";
        if (session()->has($sessionKey)) {
            $saved = session($sessionKey);
            $clinics = $this->all($officeId);
            if (is_numeric($saved) && array_key_exists((int) $saved, $clinics)) {
                return (int) $saved;
            }
        }

        return null;
    }

    /**
     * Set and persist the active clinic number for an office.
     */
    public function setActiveClinicNum(int $clinicNum, ?int $officeId = null): void
    {
        $officeId = $officeId ?? Office::getActiveOfficeId();
        if ($officeId) {
            session(["active_clinic_id_{$officeId}" => $clinicNum]);
        }
    }
}
