<?php

namespace App\Enums;

enum AppointmentStatus: int
{
    case Scheduled = 1;
    case Complete = 2;
    case Unscheduled = 3;
    case ASAP = 4;
    case Broken = 5;
    case Planned = 6;
    case PtNote = 7;
    case PtNoteCompleted = 8;
    case WaitingList = 9;

    /** Stable machine name, as the Developer API reports and filters statuses. */
    public function slug(): string
    {
        return match ($this) {
            self::Scheduled => 'scheduled',
            self::Complete => 'complete',
            self::Unscheduled => 'unscheduled',
            self::ASAP => 'asap',
            self::Broken => 'broken',
            self::Planned => 'planned',
            self::PtNote => 'patient_note',
            self::PtNoteCompleted => 'patient_note_completed',
            self::WaitingList => 'waiting_list',
        };
    }

    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }

        return null;
    }

    public function text(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Complete => 'Complete',
            self::Unscheduled => 'Unscheduled',
            self::ASAP => 'ASAP',
            self::Broken => 'Broken',
            self::Planned => 'Planned',
            self::PtNote => 'Patient Note',
            self::PtNoteCompleted => 'Patient Note Completed',
            self::WaitingList => 'Waiting List',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Complete => '#10b981', // Emerald
            self::Broken => '#ef4444', // Red
            self::ASAP => '#f59e0b', // Amber/Orange
            default => '#3b82f6', // Blue
        };
    }
}
