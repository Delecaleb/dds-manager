<?php

namespace App\Domain\FrontDesk;

/**
 * The AI Front Desk vocabulary: how a call ended, what the caller wanted, why a call or a
 * new-patient booking failed. One definition, used by every page, chart legend and status
 * pill. These labels mirror the reference product (arini-ui/); the call platform's own
 * codes are mapped onto these keys when it is connected.
 */
final class FrontDeskTaxonomy
{
    /** Status pill tones → Tailwind classes. */
    public const TONES = [
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
        'slate' => 'border-slate-200 bg-slate-50 text-slate-600',
        'violet' => 'border-violet-200 bg-violet-50 text-violet-700',
    ];

    /**
     * How a call ended. Colour = chart series colour; tone = status pill.
     *
     * @var array<string, array{label: string, color: string, tone: string}>
     */
    public const OUTCOMES = [
        'booked' => ['label' => 'Booked', 'color' => '#22c55e', 'tone' => 'emerald'],
        'confirmed' => ['label' => 'Confirmed Appt.', 'color' => '#15803d', 'tone' => 'emerald'],
        'rescheduled' => ['label' => 'Rescheduled', 'color' => '#0f766e', 'tone' => 'emerald'],
        'cancelled' => ['label' => 'Cancelled Appt.', 'color' => '#7dd3fc', 'tone' => 'sky'],
        'assisted' => ['label' => 'Assisted', 'color' => '#14b8a6', 'tone' => 'sky'],
        'transferred' => ['label' => 'Transferred', 'color' => '#a8a29e', 'tone' => 'slate'],
        'no_speech' => ['label' => 'No speech', 'color' => '#64748b', 'tone' => 'slate'],
        'no_answer' => ['label' => 'No Answer', 'color' => '#d6b98c', 'tone' => 'slate'],
        'voicemail' => ['label' => 'Voicemail', 'color' => '#94a3b8', 'tone' => 'slate'],
        'dropped' => ['label' => 'Dropped', 'color' => '#f28b82', 'tone' => 'rose'],
        'incomplete_booking' => ['label' => 'Incomplete Booking', 'color' => '#e7c36a', 'tone' => 'amber'],
        'incomplete_intake' => ['label' => 'Incomplete Intake', 'color' => '#c2842f', 'tone' => 'amber'],
        'incomplete_confirmation' => ['label' => 'Incomplete Confirmation', 'color' => '#d08a4a', 'tone' => 'amber'],
        'incomplete_reschedule' => ['label' => 'Incomplete Reschedule', 'color' => '#b59b5b', 'tone' => 'amber'],
        'incomplete_cancellation' => ['label' => 'Incomplete Cancellation', 'color' => '#cbb58b', 'tone' => 'amber'],
        'no_suitable_time' => ['label' => 'No suitable time', 'color' => '#5b8fb9', 'tone' => 'amber'],
        'no_accepted_insurance' => ['label' => 'No accepted insurance', 'color' => '#5eead4', 'tone' => 'amber'],
        'failed_booking' => ['label' => 'Failed Booking', 'color' => '#e8836f', 'tone' => 'rose'],
        'failed_confirmation' => ['label' => 'Failed Confirmation', 'color' => '#e0917f', 'tone' => 'rose'],
        'failed_cancellation' => ['label' => 'Failed Cancellation', 'color' => '#b91c1c', 'tone' => 'rose'],
        'failed_rescheduling' => ['label' => 'Failed Rescheduling', 'color' => '#dc2626', 'tone' => 'rose'],
        'other' => ['label' => 'Other', 'color' => '#6b8fd6', 'tone' => 'sky'],
    ];

    /**
     * What a transferred caller was asking about.
     *
     * @var array<string, array{label: string, color: string}>
     */
    public const INTENTS = [
        'book' => ['label' => 'Book Appointment', 'color' => '#e0a863'],
        'representative' => ['label' => 'Speak With Representative', 'color' => '#b39ddb'],
        'returning_call' => ['label' => 'Returning A Call', 'color' => '#7fb88a'],
        'specific_person' => ['label' => 'Wants To Speak To A Specific Person', 'color' => '#e8a87c'],
        'confirming' => ['label' => 'Confirming An Upcoming Appointment', 'color' => '#d9a05b'],
        'reschedule' => ['label' => 'Reschedule Appointment', 'color' => '#86c29a'],
        'cancel' => ['label' => 'Cancel Appointment', 'color' => '#7cc7c0'],
        'billing' => ['label' => 'Billing Question', 'color' => '#e08a8a'],
        'medical_records' => ['label' => 'Question Regarding Medical Records', 'color' => '#6b9bd1'],
        'treatment_followup' => ['label' => 'Treatment Follow-up', 'color' => '#94a3b8'],
        'running_late' => ['label' => 'Running Late For Appointment', 'color' => '#c4a7e7'],
        'assistant_transfer' => ['label' => 'Assistant Transfer', 'color' => '#5fa37a'],
        'unsupported_type' => ['label' => 'Not Supported Appointment Type', 'color' => '#c9a86a'],
        'no_record' => ['label' => 'Did Not Find Patient Record', 'color' => '#d4a373'],
        'update_details' => ['label' => 'Update Existing Patient Details', 'color' => '#6fbf9f'],
        'prescription' => ['label' => 'Prescription', 'color' => '#8b95a5'],
        'medical_advice' => ['label' => 'Medical Advice', 'color' => '#5b7fc7'],
        'other' => ['label' => 'Other', 'color' => '#cbd5e1'],
    ];

    /** Why a call didn't end successfully. */
    public const FAILURE_REASONS = [
        'no_one_spoke' => 'No one spoke',
        'unknown' => 'Unknown',
        'caller_quiet' => 'Caller went quiet',
        'hard_to_hear' => 'Hard to hear',
        'transfer_failed' => 'Transfer failed',
        'caller_frustrated' => 'Caller frustrated',
        'slow_responses' => 'Slow responses',
        'asked_for_person' => 'Caller asked for a person',
        'no_open_slots' => 'No open slots',
        'needed_teammate' => 'Needed a teammate',
        'no_matching_time' => 'No matching time',
        'conversation_looped' => 'Conversation looped',
        'ghost_confirmation' => 'Ghost Confirmation',
        'insurance_not_accepted' => 'Insurance not accepted',
        'no_issue' => 'No issue detected',
        'unneeded_transfer' => 'Unneeded transfer',
        'language_mismatch' => 'Agent Language Mismatch',
        'speaking_over' => 'Agent Speaking Over',
        'other' => 'Other',
    ];

    /** Settings sub-pages, in nav order: slug => [label, group]. */
    public const SETTINGS_SECTIONS = [
        'general' => ['General', 'Practice'],
        'hours' => ['Hours', 'Practice'],
        'ai-actions' => ['AI Actions', 'Practice'],
        'knowledge-base' => ['Knowledge Base', 'Practice'],
        'payment-options' => ['Payment Options', 'Practice'],
        'notifications' => ['Notifications', 'Practice'],
        'online-scheduling' => ['Online Scheduling', 'Practice'],
        'phone-numbers' => ['Phone Numbers', 'Practice'],
        'team' => ['Manage Team', 'Organization'],
    ];

    /** Patient-type series colours, shared by every new/existing chart. */
    public const EXISTING_COLOR = '#7fb88a';

    public const NEW_COLOR = '#6b9bd1';

    public static function outcome(string $key): array
    {
        return self::OUTCOMES[$key] ?? self::OUTCOMES['other'];
    }

    /** Tailwind classes for a status pill. */
    public static function toneClasses(string $tone): string
    {
        return self::TONES[$tone] ?? self::TONES['slate'];
    }
}
