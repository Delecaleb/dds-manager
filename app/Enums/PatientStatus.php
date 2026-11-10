<?php

namespace App\Enums;

/**
 * OpenDental patient.PatStatus (Enum:PatientStatus).
 */
enum PatientStatus: int
{
    case Patient = 0;
    case NonPatient = 1;
    case Inactive = 2;
    case Archived = 3;
    case Deleted = 4;
    case Deceased = 5;
    case Prospective = 6;

    /**
     * A stored PatStatus, which the synced data holds as either the number or the name.
     */
    public static function fromStored(mixed $stored): ?self
    {
        foreach (self::cases() as $case) {
            if ((string) $stored === (string) $case->value || strcasecmp((string) $stored, $case->name) === 0) {
                return $case;
            }
        }

        return null;
    }

    /**
     * What a deleted patient looks like in the synced PatStatus column.
     *
     * @return list<string>
     */
    public static function deletedValues(): array
    {
        return [(string) self::Deleted->value, self::Deleted->name];
    }

    /** Lower-case name, as the Developer API returns it. */
    public function slug(): string
    {
        return strtolower($this->name);
    }

    public function text(): string
    {
        return match ($this) {
            self::Patient => 'Patient',
            self::NonPatient => 'NonPatient',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
            self::Deleted => 'Deleted',
            self::Deceased => 'Deceased',
            self::Prospective => 'Prospective',
        };
    }
}
