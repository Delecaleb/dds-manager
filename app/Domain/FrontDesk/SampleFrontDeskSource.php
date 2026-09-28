<?php

namespace App\Domain\FrontDesk;

use App\Domain\Support\Location;
use Carbon\CarbonImmutable;

/**
 * Demo data for reviewing the AI Front Desk UI before a call platform is connected.
 * Opt-in per request (?preview=1), always badged "Sample data" in the header, and never
 * used for any real number. Names are invented and phone numbers use the reserved
 * 555-01xx range. Values are deterministic, so the same page always looks the same.
 */
class SampleFrontDeskSource extends EmptyFrontDeskSource
{
    public function label(): string
    {
        return 'Sample data — not real calls';
    }

    /** 0..1, stable for the same seed and index. */
    private function noise(string $seed, int $i = 0): float
    {
        return (crc32($seed.'#'.$i) % 1000) / 1000;
    }

    /** How many offices the filter covers, as a volume multiplier. */
    private function scale(FrontDeskFilter $filter): float
    {
        return max(1, $filter->locations !== [] ? count($filter->locations) : count($this->clinics->locations()));
    }

    protected function values(string $key, array $labels, FrontDeskFilter $filter): array
    {
        [$chart, $series] = explode('.', $key, 2);
        $scale = $this->scale($filter);
        $days = $filter->days();
        $out = [];

        foreach ($labels as $i => $label) {
            $weekday = count($labels) === count($days) && isset($days[$i]) ? ! $days[$i]->isWeekend() : true;
            $n = $this->noise($key, $i);
            $dayCalls = $weekday ? (30 + 18 * $n) * $scale : 2 * $n * $scale;

            $out[] = match ($chart) {
                'booked' => (int) round(($weekday ? 2.2 : 0.1) * $scale * (0.5 + $n) * ($series === 'new' ? 0.9 : 1)),
                'callVolume' => (int) round($series === 'resolved' ? $dayCalls * (0.5 + 0.1 * $n) : $dayCalls),
                'callsByHour' => (int) round(exp(-(($i - 12.5) ** 2) / 10) * 90 * $scale * ($series === 'new' ? 0.55 : 1) * (0.85 + 0.3 * $n)),
                'successRate' => round(($series === 'new' ? 78 : 50) + 8 * $n, 1),
                'transferRate' => round(($series === 'new' ? 4 : 8) + 5 * $n, 1),
                'closeRate' => round(($series === 'new' ? 52 : 58) + 12 * $n, 1),
                'intakeActions' => (int) round(['booked' => 24, 'cancelled' => 5, 'confirmed' => 18, 'rescheduled' => 7][$series] * $scale * (0.6 + 0.6 * $n)),
                'dailyOutcomes' => $this->share($series, FrontDeskTaxonomy::OUTCOMES, $dayCalls, $n),
                'transferIntents' => $this->share($series, FrontDeskTaxonomy::INTENTS, $dayCalls * 0.3, $n),
                default => null,
            };
        }

        return $out;
    }

    /** A category's slice of a daily total: the first categories get the biggest share. */
    private function share(string $key, array $categories, float $total, float $n): int
    {
        $rank = array_search($key, array_keys($categories), true);

        return (int) round($total * (0.28 / (1 + $rank * 0.9)) * (0.6 + 0.8 * $n));
    }

    protected function summary(FrontDeskFilter $filter): array
    {
        $scale = $this->scale($filter);
        $calls = (int) round(1110 * $scale);
        $new = (int) round(43 * $scale);
        $existing = (int) round(44 * $scale);

        return [
            'offices_live' => count($this->offices($filter)),
            'offices_total' => count($this->offices($filter)),
            'calls' => $calls, 'calls_change' => -7.0,
            'booked' => $new + $existing, 'booked_new' => $new, 'booked_existing' => $existing, 'booked_change' => -12.0,
            'production' => round(30100 * $scale, 2), 'production_change' => -31.0,
            'success_rate' => 55.0, 'success_change' => 11.0,
            'transfer_rate' => 27.0, 'transfer_change' => -11.0,
            'close_rate' => 53.7,
            'time_saved' => (int) round($calls * 0.87), 'time_saved_change' => 20.0, 'avg_minutes' => 1.6,
            'unsuccessful' => (int) round($calls * 0.45), 'missed_np' => (int) round(30 * $scale),
        ];
    }

    protected function ranked(string $key, array $labels, FrontDeskFilter $filter): array
    {
        $scale = $this->scale($filter);
        $rows = [];
        foreach (array_slice($labels, 0, $key === 'bookingGaps' ? 12 : 19) as $i => $label) {
            $rows[] = ['label' => $label, 'count' => (int) max(1, round(220 * $scale / (1 + $i * 1.6) * (0.7 + 0.6 * $this->noise($key, $i))))];
        }
        usort($rows, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $rows;
    }

    protected function officeTotals(Location $location, FrontDeskFilter $filter): array
    {
        $n = $this->noise($location->key());
        $calls = (int) round(80 + 3600 * $n * $n);

        return [
            'status' => 'active',
            'calls' => $calls,
            'appts' => (int) round($calls * (0.02 + 0.08 * $this->noise($location->key(), 1))),
            'production' => round($calls * (4 + 30 * $this->noise($location->key(), 2)), 2),
        ];
    }

    public function calls(Location $location): array
    {
        $people = [
            ['Jordan Blake', 'transferred', 'representative', 116, 'Jordan called to ask about an upcoming appointment and the braces treatment plan, then asked to speak with someone at the front desk.'],
            [null, 'no_speech', null, 3, null],
            [null, 'no_speech', null, 2, null],
            [null, 'other', 'other', 41, 'The caller asked whether the office is open on Saturdays and ended the call after hearing the hours.'],
            [null, 'no_speech', null, 4, null],
            ['Avery Collins', 'booked', 'book', 212, 'Avery booked a new-patient orthodontic consultation for next Tuesday at 10:00 AM and shared PPO insurance details.'],
            ['Morgan Reyes', 'transferred', 'billing', 95, 'Morgan had a question about a balance on the last statement and was transferred to billing.'],
            ['Taylor Brooks', 'transferred', 'specific_person', 64, 'Taylor asked to speak with the treatment coordinator by name and was transferred.'],
            [null, 'dropped', null, 18, 'The call dropped while the assistant was looking up available times.'],
            ['Riley Parker', 'confirmed', 'confirming', 58, 'Riley confirmed the appointment on Thursday at 2:30 PM.'],
            ['Casey Morgan', 'rescheduled', 'reschedule', 143, 'Casey moved Friday\'s adjustment to the following Monday at 9:00 AM.'],
            ['Quinn Harper', 'incomplete_booking', 'book', 97, 'Quinn wanted an evening appointment; no suitable time was found and the caller said they would call back.'],
        ];

        $start = CarbonImmutable::today()->subDay()->setTime(19, 39);
        $calls = [];
        foreach ($people as $i => [$name, $outcome, $intent, $seconds, $summary]) {
            $phone = sprintf('(313) 555-01%02d', 10 + $i * 7);
            $calls[] = [
                'id' => 'call-'.($i + 1),
                'name' => $name,
                'phone' => $phone,
                'outcome' => $outcome,
                'direction' => $i === 9 ? 'outbound' : 'inbound',
                'time' => $start->subMinutes($i * 97)->toIso8601String(),
                'triaged' => $i > 8,
                'tasks' => $i === 6 ? 1 : 0,
                'duration' => $seconds,
                'summary' => $summary,
                'intent' => $intent ? FrontDeskTaxonomy::INTENTS[$intent]['label'] : null,
                'dob' => $name ? CarbonImmutable::create(1980 + $i * 3, 1 + $i, 10 + $i)->toDateString() : null,
                'transcript' => $name ? [
                    ['speaker' => 'assistant', 'text' => 'Thanks for calling '.$location->name.', this is Sophie. How can I help you today?'],
                    ['speaker' => 'caller', 'text' => 'Hi, I had a question about my appointment.'],
                    ['speaker' => 'assistant', 'text' => 'Of course. Can I get your first and last name, and your date of birth?'],
                    ['speaker' => 'caller', 'text' => 'Sure, it\'s '.$name.'.'],
                ] : [],
            ];
        }

        return $calls;
    }

    public function call(Location $location, string $id): ?array
    {
        return collect($this->calls($location))->firstWhere('id', $id);
    }

    public function conversations(Location $location): array
    {
        $threads = [
            ['Dana Whitfield', 'yes but i need to get a cleaning before that', true],
            [null, 'We do offer cleanings, but I\'d need to check with the team first.', true],
            ['Tessa Allen', 'Hi Tessa! We noticed it\'s been a little while since your last visit…', true],
            [null, 'I understand — it sounds like you\'re waiting on a callback from our team.', true],
            ['Cameron Tate', 'Hi Cameron! We noticed it\'s been a little while since your last visit…', true],
            ['Jesse Warren', 'Thanks 😀', true],
            ['Drew Jordan', 'Yes', true],
            ['Nico Bell', 'Yes that works', true],
            [null, 'Hi! This is Sophie from '.$location->name.'…', false],
        ];

        $out = [];
        foreach ($threads as $i => [$name, $preview, $unread]) {
            $out[] = [
                'id' => 'thread-'.($i + 1),
                'name' => $name,
                'phone' => sprintf('(313) 555-01%02d', 40 + $i * 5),
                'preview' => $preview,
                'unread' => $unread ? 1 : 0,
                'agent_on' => $i !== 3,
                'time' => CarbonImmutable::today()->subDays($i < 8 ? 5 : 2)->setTime(19, 16)->toIso8601String(),
            ];
        }

        return $out;
    }

    public function conversation(Location $location, string $id): ?array
    {
        $thread = collect($this->conversations($location))->firstWhere('id', $id);
        if (! $thread) {
            return null;
        }

        $base = CarbonImmutable::today()->subDays(5)->setTime(18, 54);

        return $thread + ['messages' => [
            ['from' => 'office', 'text' => 'Hi! This is '.$location->name.'. Thank you for confirming your appointment on Nov 4 at 10:00 AM. Please don\'t hesitate to call or text us if you have any questions. We look forward to seeing you!', 'time' => $base->subDays(30)->toIso8601String()],
            ['from' => 'office', 'text' => 'Hi! We noticed it\'s been a little while since your last visit with us at '.$location->name.'. We\'d love to get you back on track! Would you like to schedule an adjustment or a consultation?', 'time' => $base->toIso8601String()],
            ['from' => 'patient', 'text' => 'Someone from your office was supposed to give us a call to let us know when his next appointment is', 'time' => $base->addMinutes(22)->toIso8601String()],
            ['from' => 'office', 'text' => 'I understand — it sounds like you\'re waiting on a callback from our team about his next appointment. I\'m not able to handle that directly, but I\'ll let the practice know to follow up with you. They\'ll reach out soon!', 'time' => $base->addMinutes(22)->toIso8601String()],
            ['from' => 'system', 'text' => 'Human intervention required — SMS agent turned off', 'time' => $base->addMinutes(22)->toIso8601String()],
        ]];
    }

    public function messageActions(Location $location): array
    {
        return [
            ['id' => 'action-1', 'thread' => 'thread-4', 'phone' => '(313) 555-0155', 'reason' => 'Waiting on a callback about the next appointment', 'time' => CarbonImmutable::today()->subDays(5)->setTime(19, 16)->toIso8601String(), 'done' => false],
            ['id' => 'action-2', 'thread' => 'thread-2', 'phone' => '(313) 555-0145', 'reason' => 'Wants a cleaning before the scheduled visit', 'time' => CarbonImmutable::today()->subDays(5)->setTime(17, 2)->toIso8601String(), 'done' => false],
        ];
    }

    public function bookings(Location $location): array
    {
        $rows = [
            ['Harper Mitchell', 22, 14, 'Ortho Consultation'],
            ['Kayla Whitmore', 85, 16.5, 'Ortho Consultation'],
            ['Kendra Williams', 1, 10, 'New Patient Exam'],
            ['Jordan Blake', 3, 16.5, 'Adjustment'],
            ['Lana Sullivan', 8, 13, 'Ortho Consultation'],
            ['Mia Barry', 11, 16.5, 'New Patient Exam'],
            ['Sydney Dennis', 1, 9.5, 'Ortho Consultation'],
            ['Chris Baker', 1, 9.5, 'Adjustment'],
        ];

        $out = [];
        foreach ($rows as $i => [$name, $inDays, $hour, $service]) {
            $appt = CarbonImmutable::today()->addDays($inDays)->setTime((int) $hour, $hour - (int) $hour > 0 ? 30 : 0);
            $out[] = [
                'id' => 'booking-'.($i + 1),
                'name' => $name,
                'status' => 'booked',
                'appointment' => $appt->toIso8601String(),
                'created' => CarbonImmutable::today()->subDays(intdiv($i, 3))->setTime(5 + $i, 31)->toIso8601String(),
                'phone' => sprintf('(313) 555-01%02d', 60 + $i * 3),
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
                'dob' => CarbonImmutable::create(1990 + $i, 6, 25)->toDateString(),
                'service' => $service,
                'provider' => 'Dr. Sample Provider',
                'summary' => "{$name} booked an appointment for {$service} on ".$appt->format('M j, Y \a\t g:i A').'.',
            ];
        }

        return $out;
    }

    public function booking(Location $location, string $id): ?array
    {
        return collect($this->bookings($location))->firstWhere('id', $id);
    }

    public function workflows(Location $location): array
    {
        return [
            [
                'id' => 'confirmation',
                'name' => 'Confirmation Workflow',
                'enabled' => false,
                'summary' => null,
                'instructions' => '',
                'runs' => [],
            ],
            [
                'id' => 'reactivation',
                'name' => 'Reactivation Workflow',
                'enabled' => true,
                'summary' => 'Reached 130 of 206 scheduled (76 short). 12 replied, 2 booked.',
                'instructions' => "Tone: warm, concise, and human — write like the front desk at {$location->name} texting a patient they already know. Greet by first name, mention the practice by name, and keep it low-pressure.\n\nWho to reach out to:\n- Active patients only. Skip anyone marked inactive, deceased, or moved out of the area.\n- Skip patients who already have an upcoming appointment on the books.",
                'runs' => [[
                    'time' => CarbonImmutable::today()->subDays(4)->setTime(18, 12)->toIso8601String(),
                    'patients' => 130,
                    'messages' => [
                        ['phone' => '+13135550182', 'text' => "Hi Sam! We noticed it's been a little while since your last visit with us at {$location->name}. We'd love to get you back on track!", 'time' => CarbonImmutable::today()->subDays(5)->toIso8601String()],
                    ],
                ]],
            ],
        ];
    }

    public function schedule(Location $location, string $date): array
    {
        $columns = ['DR-1', 'DR-2', 'DR-3', 'DR-4', 'DR-5', 'HYG-6', 'HYG-7', 'HYG-8'];
        $patients = ['King, Pat', 'Williams, Bri', 'Cooper, Tas', 'Lacy, Lane', 'Wright, Lee', 'Garcia, Yen', 'Carson, Jay', 'Jackson, Ami', 'Perry, Mal', 'Peoples, Myra', 'Neal, Dom', 'Knight, Jac', 'Onim, Mo', 'Jones, Kia'];
        $appointments = [];
        $p = 0;
        foreach (array_slice($columns, 0, 5) as $c => $column) {
            $minute = 9 * 60;
            while ($minute < 17 * 60) {
                if ($minute >= 12 * 60 && $minute < 13 * 60) {
                    $minute = 13 * 60;

                    continue;
                }
                $length = $this->noise($column, $minute) > 0.5 ? 60 : 30;
                if ($this->noise($column.'gap', $minute) > 0.25) {
                    $appointments[] = [
                        'column' => $c,
                        'start' => $minute,
                        'end' => $minute + $length,
                        'patient' => $patients[$p++ % count($patients)],
                    ];
                }
                $minute += $length;
            }
        }

        return [
            'columns' => $columns,
            'appointments' => $appointments,
            'appointmentTypes' => ['Existing Patient Orthodontic Consultation', 'New Patient Orthodontic Consultation'],
        ];
    }

    public function settings(Location $location): array
    {
        $settings = parent::settings($location);
        $week = fn (array|false $weekend) => [
            'Monday' => ['9am', '5pm'], 'Tuesday' => ['9am', '5pm'], 'Wednesday' => ['9am', '5pm'],
            'Thursday' => ['9am', '5pm'], 'Friday' => ['9am', '5pm'], 'Saturday' => $weekend, 'Sunday' => $weekend,
        ];

        $flowNew = [
            ['Appointment Type', 'Determine the type of appointment the patient is looking to book.'],
            ['New Or Existing Patient', 'Determine if new or existing patient.'],
            ['Suitable Time', 'Find a suitable appointment time.'],
            ['Patient Name And Spelling', 'Collect first and last name.'],
            ['Basic Info', 'Collect date of birth and phone number.'],
            ['Insurance Type', 'Collect insurance plan type (PPO, HMO, Medicaid, Medicare, etc.).'],
            ['Insurance Name', 'Collect insurance plan name or note self-pay.'],
            ['Insurance Id', 'Collect insurance ID number.'],
            ['Referral', 'Collect referral source.'],
        ];
        $flowExisting = [
            ['Appointment Type', 'Determine the type of appointment the patient is looking to book.'],
            ['Identify Caller', 'Identify the patient by their name, date of birth, and phone number.'],
            ['Suitable Time', 'Find a suitable appointment time.'],
        ];
        $template = 'Hi <first_name>, welcome to '.$location->name.'! You have an appointment reserved on <appointment_date> at <appointment_start_time>. We look forward to seeing you!';

        return array_replace($settings, [
            'general' => array_replace($settings['general'], [
                'assistant_name' => 'Sophie',
                'phone' => '(313) 555-0100',
                'email' => 'frontdesk@example.com',
                'services' => ['Braces & Orthodontics', 'Invisalign', 'Ortho Screenings', 'Whitening'],
            ]),
            'hours' => [
                'closures' => array_map(fn ($d) => CarbonImmutable::parse($d)->toDateString(), ['+7 days', '+37 days', '+58 days', '+59 days']),
                'business' => $week(['9am', '5pm']),
                'admin' => $week(false),
            ],
            'ai-actions' => [
                'new' => [[
                    'name' => 'New Patient Orthodontic Consultation', 'duration' => '30 minutes', 'services' => 'Brief Exam',
                    'codes' => 'D8090, NCCN', 'blocked' => ['monday, wednesday, saturday, sunday: 8:00 AM - 5:00 PM'],
                    'providers' => [['DR-5', 'Dr. Sample Provider'], ['DR-4', 'Dr. Sample Provider']],
                    'flow' => $flowNew, 'template' => $template, 'template_es' => null,
                ]],
                'existing' => [[
                    'name' => 'Existing Patient Orthodontic Consultation', 'duration' => '30 minutes', 'services' => 'Brief Exam',
                    'codes' => 'D8670, NCCN', 'blocked' => ['monday, wednesday, saturday, sunday: 8:00 AM - 5:00 PM'],
                    'providers' => [['DR-1', 'Dr. Sample Provider'], ['DR-2', 'Dr. Sample Provider']],
                    'flow' => $flowExisting, 'template' => $template, 'template_es' => null,
                ]],
                'actions' => [
                    ['name' => 'Cancel Existing Patient Appointment', 'flow' => [['Upsell Reschedule Appointment', 'Offer to reschedule instead of cancelling.'], ['Identify Caller', 'Identify the patient by their name, date of birth, and phone number.'], ['Cancel Existing Patient Appointment', 'Cancel upcoming appointment.']], 'template' => 'Hi <first_name>, we cancelled your appointment on <appointment_date> at <appointment_start_time>. Please call us when you\'re ready to reschedule!', 'template_es' => 'Hola <first_name>, hemos cancelado su cita el <appointment_date> a las <appointment_start_time>.'],
                    ['name' => 'Confirm Existing Patient Appointment', 'flow' => [['Identify Caller', 'Identify the patient by their name, date of birth, and phone number.'], ['Confirm Existing Patient Appointment', 'Confirm upcoming appointment attendance.']], 'template' => 'Hi <first_name>! Thank you for confirming your appointment on <appointment_date> at <appointment_start_time>.', 'template_es' => null],
                ],
            ],
            'knowledge-base' => ['content' => "Q: Where are you located?\nA: We are at the address on your appointment reminder. Can I help you with anything else?\n\nQ: What's your fax number?\nA: Our fax number is 313-555-0199. Can I help you with anything else?"],
            'payment-options' => [
                'packages' => [['name' => 'New Patient Initial Exam and full set of x-rays', 'price' => 99, 'details' => ['Comprehensive Exam, X-rays', 'Appointment types: Initial Exam', 'Patient type: New patients']]],
                'insurance' => [
                    ['name' => 'All PPO', 'note' => 'All PPO plans', 'type' => 'PPO', 'accepted' => true],
                    ['name' => 'All HMO', 'note' => 'All HMO plans', 'type' => 'HMO', 'accepted' => true],
                    ['name' => 'Medicaid', 'note' => 'State Medicaid plans', 'type' => 'OTHER', 'accepted' => true],
                ],
                'financing' => 'We accept CareCredit as well as Cherry Financing.',
            ],
            'notifications' => array_replace($settings['notifications'], ['recipients' => [
                ['type' => 'Call Completed (recommended)', 'method' => 'email', 'destination' => 'frontdesk@example.com'],
                ['type' => 'SMS: Assistant Needed', 'method' => 'email', 'destination' => 'frontdesk@example.com'],
            ]]),
            'online-scheduling' => [
                'link' => url('/book/sample-location'),
                'redirect' => 'https://example.com/thank-you/',
                'services' => ['new' => ['Orthodontic Consultation (e.g. Braces, Invisalign, …etc)'], 'existing' => ['Adjustment']],
                'recipients' => [],
            ],
            'phone-numbers' => [
                'agent' => [['number' => '(313) 555-0120', 'designation' => 'live'], ['number' => '(313) 555-0121', 'designation' => 'testing']],
                'transfer' => [['number' => '(313) 555-0130', 'sms' => true, 'reasons' => ['Back Line Transfers']]],
            ],
            'team' => ['members' => [['name' => 'Front Desk Lead', 'email' => 'lead@example.com', 'role' => 'Admin']]],
        ]);
    }
}
