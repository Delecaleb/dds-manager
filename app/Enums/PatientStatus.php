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
