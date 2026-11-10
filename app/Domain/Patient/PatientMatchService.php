<?php

namespace App\Domain\Patient;

use App\Domain\Support\MetricFilter;
use App\Enums\PatientStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Links a person known to another system to their OpenDental patient record: first name,
 * last name and birth date must all match, and a phone number only narrows the result when
 * several patients share them. Only a single remaining patient counts as a match; anything
 * else is left for a person to review, because linking the wrong patient exposes their care.
 *
 * Reads the local synced copy, so a new patient matches after their record has synced.
 */
class PatientMatchService
{
    public const MATCHED = 'matched';

    public const NONE = 'none';

    public const MULTIPLE = 'multiple';

    private const COLUMNS = [
        'PatNum', 'FName', 'LName', 'Birthdate', 'PatStatus', 'ClinicNum', 'DateFirstVisit',
        'HmPhone', 'WkPhone', 'WirelessPhone',
    ];

    /**
     * One outcome per person, in the order given. All birth dates are read in one query, so
     * a batch costs the same as a single lookup.
     *
     * @param  list<array{reference?: ?string, first_name: string, last_name: string, birthdate: string, phone?: ?string}>  $people
     * @return list<array{reference: ?string, result: string, patient: ?object, candidates: list<object>}>
     */
    public function match(MetricFilter $filter, array $people): array
    {
        $patients = $this->scoped($filter)
            ->whereIn('Birthdate', array_values(array_unique(array_column($people, 'birthdate'))))
            ->where(fn (Builder $q) => $q->whereNull('PatStatus')->orWhereNotIn('PatStatus', PatientStatus::deletedValues()))
            ->get(self::COLUMNS)
            ->groupBy(fn (object $patient) => substr((string) $patient->Birthdate, 0, 10));

        return array_map(
            fn (array $person) => $this->outcome($person, $patients->get($person['birthdate'], new Collection)),
            $people,
        );
    }

    /** One patient by PatNum, deleted ones included so a caller can see a link has gone stale. */
    public function find(MetricFilter $filter, int $patNum): ?object
    {
        return $this->scoped($filter)->where('PatNum', $patNum)->first(self::COLUMNS);
    }

    private function scoped(MetricFilter $filter): Builder
    {
        return DB::table('od_patients')
            ->where('office_id', $filter->officeId)
            ->when($filter->clinics !== [], fn (Builder $q) => $q->whereIn('ClinicNum', $filter->clinics));
    }

    /**
     * @param  array{reference?: ?string, first_name: string, last_name: string, phone?: ?string}  $person
     * @param  Collection<int, object>  $sameBirthdate
     * @return array{reference: ?string, result: string, patient: ?object, candidates: list<object>}
     */
    private function outcome(array $person, Collection $sameBirthdate): array
    {
        $candidates = $sameBirthdate
            ->filter(fn (object $p) => $this->sameName($p->FName, $person['first_name']) && $this->sameName($p->LName, $person['last_name']))
            ->values();

        if ($candidates->count() > 1) {
            $byPhone = $this->withPhone($candidates, $person['phone'] ?? null);
            $candidates = $byPhone->isNotEmpty() ? $byPhone : $candidates;
        }

        $single = $candidates->count() === 1;

        return [
            'reference' => $person['reference'] ?? null,
            'result' => match (true) {
                $single => self::MATCHED,
                $candidates->isEmpty() => self::NONE,
                default => self::MULTIPLE,
            },
            'patient' => $single ? $candidates->first() : null,
            'candidates' => $single ? [] : $candidates->all(),
        ];
    }

    private function sameName(?string $stored, string $given): bool
    {
        return mb_strtolower(trim((string) $stored)) === mb_strtolower(trim($given));
    }

    /**
     * @param  Collection<int, object>  $patients
     * @return Collection<int, object> those holding the number, whichever way it was typed
     */
    private function withPhone(Collection $patients, ?string $phone): Collection
    {
        $wanted = $this->lastTenDigits($phone);

        if ($wanted === '') {
            return new Collection;
        }

        return $patients->filter(fn (object $p) => in_array(
            $wanted,
            array_map($this->lastTenDigits(...), [$p->WirelessPhone, $p->HmPhone, $p->WkPhone]),
            true,
        ))->values();
    }

    private function lastTenDigits(?string $phone): string
    {
        return substr(preg_replace('/\D+/', '', (string) $phone) ?? '', -10);
    }
}
