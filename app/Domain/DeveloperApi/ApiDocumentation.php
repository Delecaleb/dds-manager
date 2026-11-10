<?php

namespace App\Domain\DeveloperApi;

use App\Enums\AppointmentStatus;
use App\Enums\PatientStatus;
use App\Models\ApiKey;

/**
 * The Developer API reference shown in the Developer Portal: one entry per endpoint, and the
 * only place endpoints are described. Adding a route under /api means adding its entry here;
 * tests fail when a route has no entry or an example no longer matches the real response.
 */
class ApiDocumentation
{
    /**
     * Endpoints in reading order, grouped by what they are for.
     *
     * @return list<array{title: string, description: string, endpoints: list<array<string, mixed>>}>
     */
    public function groups(): array
    {
        return [
            [
                'title' => 'Your key',
                'description' => 'Confirm a key works and see what it can read.',
                'endpoints' => [$this->me()],
            ],
            [
                'title' => 'Bookings',
                'description' => 'Appointments for showing availability or keeping another calendar up to date.',
                'endpoints' => [$this->bookingsIndex(), $this->bookingsShow()],
            ],
            [
                'title' => 'Patients and care',
                'description' => 'Link a person in your system to their patient record, then read the care they have received.',
                'endpoints' => [$this->patientsMatch(), $this->patientsShow(), $this->patientsProcedures()],
            ],
            [
                'title' => 'Reference data',
                'description' => 'Lists that give names and contact details to the ids and codes in other responses.',
                'endpoints' => [$this->procedureCodes(), $this->clinics()],
            ],
        ];
    }

    /** @return list<array<string, mixed>> every endpoint, in reading order */
    public function endpoints(): array
    {
        return array_merge(...array_column($this->groups(), 'endpoints'));
    }

    /** @return array<string, mixed> the entry for a route name such as "api.v1.me" */
    public function endpoint(string $route): array
    {
        foreach ($this->endpoints() as $endpoint) {
            if ($endpoint['route'] === $route) {
                return $endpoint;
            }
        }

        throw new \InvalidArgumentException("No documentation for route [{$route}].");
    }

    /**
     * What a membership portal needs from this API, each need with the endpoint serving it.
     *
     * @return list<array{need: string, endpoint: array<string, mixed>}>
     */
    public function membershipNeeds(): array
    {
        return array_map(fn (array $need) => ['need' => $need[0], 'endpoint' => $this->endpoint($need[1])], [
            ['Find which patient a new member is', 'api.v1.patients.match'],
            ['Get the treatment a member has had this year', 'api.v1.patients.procedures'],
            ['Look up what a procedure code means, such as D1110 = adult cleaning', 'api.v1.procedure-codes.index'],
            ['Check that a saved patient number still belongs to a patient', 'api.v1.patients.show'],
            ['Get each clinic\'s phone number and address for the "Contact my clinic" tile', 'api.v1.clinics.index'],
        ]);
    }

    /** A complete curl command for an endpoint's example. */
    public function curl(array $endpoint, string $apiBase): string
    {
        $url = $apiBase.$endpoint['example_path'];

        if ($endpoint['example_body'] === null) {
            return "curl -H \"Authorization: Bearer dds_your_key\" \\\n  \"{$url}\"";
        }

        return "curl -X POST \"{$url}\" \\\n  -H \"Authorization: Bearer dds_your_key\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '"
            .$this->json($endpoint['example_body'])."'";
    }

    public function json(mixed $value): string
    {
        return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** Where a list response's next page is, as `links` and `meta` return it. */
    private function cursorPage(string $path, int $perPage): array
    {
        $url = url($path);

        return [
            'links' => ['first' => null, 'last' => null, 'prev' => null, 'next' => $url.'?cursor=eyJjdXJzb3IiOiIxIn0'],
            'meta' => ['path' => $url, 'per_page' => $perPage, 'next_cursor' => 'eyJjdXJzb3IiOiIxIn0', 'prev_cursor' => null],
        ];
    }

    private function me(): array
    {
        return [
            'route' => 'api.v1.me',
            'method' => 'GET',
            'path' => '/me',
            'title' => 'Check your key',
            'permission' => null,
            'summary' => 'Returns the calling key\'s name, permissions, rate limit and location, and how recent its data is.',
            'use_when' => 'You are setting up an integration, or want to check a key still works and which location it reads. Your system can also use latest_change_at to warn when data looks stale.',
            'parameters_in' => null,
            'parameters' => [],
            'example_path' => '/me',
            'example_body' => null,
            'example_response' => ['data' => [
                'key' => [
                    'name' => 'Member portal',
                    'permissions' => [ApiKey::ABILITY_BOOKINGS, ApiKey::ABILITY_PATIENT_MATCH, ApiKey::ABILITY_PROCEDURES],
                    'rate_limit_per_minute' => (int) config('developer_api.default_rate_limit'),
                    'expires_at' => null,
                ],
                'location' => ['key' => '5', 'name' => 'Example Dental (all clinics)', 'office_id' => 5, 'clinic_num' => null],
                'latest_change_at' => '2026-11-10T08:15:00',
            ]],
            'fields' => [
                ['data.key.permissions', 'list', 'The permissions this key holds. See Permissions above.'],
                ['data.key.expires_at', 'date-time or null', 'When the key stops working. Null means it does not expire.'],
                ['data.location.clinic_num', 'integer or null', 'The clinic the key is limited to. Null means the whole location.'],
                ['data.latest_change_at', 'date-time or null', 'The newest appointment change held locally, in the office\'s local time.'],
            ],
            'notes' => [],
        ];
    }

    private function bookingsIndex(): array
    {
        $maxDays = (int) config('developer_api.max_range_days');
        $maxPerPage = (int) config('developer_api.max_per_page');
        $defaultPerPage = (int) config('developer_api.per_page');
        $statuses = implode(', ', array_map(fn (AppointmentStatus $s) => $s->slug(), AppointmentStatus::cases()));

        return [
            'route' => 'api.v1.bookings.index',
            'method' => 'GET',
            'path' => '/bookings',
            'title' => 'List bookings',
            'permission' => ApiKey::ABILITY_BOOKINGS,
            'summary' => 'Returns the location\'s appointments in a date range, earliest first.',
            'use_when' => 'You need a schedule: showing booked time, copying appointments into another calendar, or picking up what changed since your last check.',
            'parameters_in' => 'Query string',
            'parameters' => [
                ['start', 'date', true, 'First day to include, as YYYY-MM-DD.'],
                ['end', 'date', true, "Last day to include, as YYYY-MM-DD. The range can cover at most {$maxDays} days."],
                ['status', 'text', false, "Only these statuses, comma separated. One of: {$statuses}."],
                ['provider', 'text', false, 'Only these providers, as comma-separated provider ids.'],
                ['updated_since', 'date-time', false, 'Only bookings changed after this time, as YYYY-MM-DDTHH:MM:SS in the office\'s local time.'],
                ['per_page', 'integer', false, "Results per page, up to {$maxPerPage}. The default is {$defaultPerPage}."],
                ['cursor', 'text', false, 'The next page\'s position. Follow links.next instead of building this yourself.'],
            ],
            'example_path' => '/bookings?start=2026-11-10&end=2026-11-23&status=scheduled',
            'example_body' => null,
            'example_response' => ['data' => [$this->exampleBooking()]] + $this->cursorPage('/api/v1/bookings', $defaultPerPage),
            'fields' => $this->bookingFields(),
            'notes' => [
                'A booking deleted in Open Dental disappears from results. Reading only updated_since changes will not tell you about it, so refresh the full range now and then.',
                'The patient object appears only when the key has the '.ApiKey::ABILITY_PATIENT_CONTACT.' permission. Without it you still get patient_id.',
            ],
        ];
    }

    private function bookingsShow(): array
    {
        return [
            'route' => 'api.v1.bookings.show',
            'method' => 'GET',
            'path' => '/bookings/{id}',
            'title' => 'Get one booking',
            'permission' => ApiKey::ABILITY_BOOKINGS,
            'summary' => 'Returns a single appointment by its id.',
            'use_when' => 'You already hold a booking id and want its current time and status, without listing a whole date range.',
            'parameters_in' => 'URL path',
            'parameters' => [
                ['id', 'integer', true, 'The booking id, from a list response.'],
            ],
            'example_path' => '/bookings/1001',
            'example_body' => null,
            'example_response' => ['data' => $this->exampleBooking()],
            'fields' => [
                ['data', 'object', 'The same fields as one item in List bookings.'],
            ],
            'notes' => ['Returns 404 when the booking does not exist, or belongs to another location.'],
        ];
    }

    private function exampleBooking(): array
    {
        return [
            'id' => 1001,
            'status' => 'scheduled',
            'start' => '2026-11-12T09:00:00',
            'end' => '2026-11-12T10:00:00',
            'duration_minutes' => 60,
            'clinic_num' => 3,
            'operatory_id' => 12,
            'provider' => ['id' => 7, 'abbreviation' => 'DRS', 'first_name' => 'Sam', 'last_name' => 'Smith'],
            'hygienist_id' => null,
            'is_new_patient' => false,
            'is_hygiene' => false,
            'procedures' => 'Ex, Pro',
            'patient_id' => 500,
            'patient' => [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'preferred_name' => null,
                'email' => 'jane@example.com',
                'phones' => ['mobile' => '313-555-0100', 'home' => null, 'work' => null],
                'address' => ['line1' => '1 Main St', 'line2' => null, 'city' => 'Ann Arbor', 'state' => 'MI', 'zip' => '48104'],
            ],
            'created_at' => '2026-11-01T10:00:00',
            'updated_at' => '2026-11-05T08:00:00',
        ];
    }

    private function bookingFields(): array
    {
        return [
            ['id', 'integer', 'The booking id.'],
            ['status', 'text', 'The appointment status, using the names listed under status above.'],
            ['start, end', 'date-time', 'In the office\'s local time, with no offset. end is null when the length is unknown.'],
            ['provider', 'object or null', 'The provider\'s id, abbreviation and name.'],
            ['procedures', 'text or null', 'Open Dental\'s short description of the planned work.'],
            ['patient_id', 'integer or null', 'The patient\'s id. Use it with the patient endpoints.'],
            ['patient', 'object', 'Name, email, phone numbers and address. Only with the '.ApiKey::ABILITY_PATIENT_CONTACT.' permission.'],
            ['updated_at', 'date-time or null', 'When the booking last changed. Keep the highest value you see for your next updated_since.'],
        ];
    }

    private function patientsMatch(): array
    {
        $maxBatch = (int) config('developer_api.max_batch');

        return [
            'route' => 'api.v1.patients.match',
            'method' => 'POST',
            'path' => '/patients/match',
            'title' => 'Match people to patients',
            'permission' => ApiKey::ABILITY_PATIENT_MATCH,
            'summary' => 'Finds the patient record for each person you send, using their first name, last name and birth date.',
            'use_when' => 'Someone in your system needs linking to their Open Dental record, for example when a membership starts. Send everyone still unlinked in one batch, and repeat on a schedule for people who are not patients yet.',
            'parameters_in' => 'JSON body',
            'parameters' => [
                ['people', 'list', true, "The people to look up, at most {$maxBatch} per request."],
                ['people[].first_name', 'text', true, 'Must match the patient\'s first name. Capitals and extra spaces are ignored; nicknames do not match.'],
                ['people[].last_name', 'text', true, 'Must match the patient\'s last name, on the same terms.'],
                ['people[].birthdate', 'date', true, 'YYYY-MM-DD.'],
                ['people[].phone', 'text', false, 'Used only when several patients share the name and birth date, to single one out. Any format works.'],
                ['people[].reference', 'text', false, 'Your own id for this person, up to 64 characters. It comes back unchanged, so you can pair up the results.'],
            ],
            'example_path' => '/patients/match',
            'example_body' => ['people' => [
                ['reference' => 'member-1042', 'first_name' => 'Jane', 'last_name' => 'Doe', 'birthdate' => '1980-05-01', 'phone' => '313-555-0100'],
            ]],
            'example_response' => ['data' => [[
                'reference' => 'member-1042',
                'result' => 'matched',
                'patient' => $this->examplePatient(),
                'candidates' => [],
            ]]],
            'fields' => [
                ['result', 'text', 'matched: one patient fits, see patient. none: no patient fits yet. multiple: more than one fits, see candidates.'],
                ['patient', 'object or null', 'The matched patient. Store patient.id. Null unless result is matched.'],
                ['candidates', 'list', 'The patients that fit when result is multiple, for a person to choose from. Empty otherwise.'],
            ],
            'notes' => [
                'Do not pick a candidate automatically when the result is multiple. Linking the wrong record would show someone another patient\'s care.',
                'Deleted patients never match. A new patient can only match once their record has reached this system\'s synced copy.',
                'A clinic-limited key only finds patients whose home clinic is that clinic.',
                'This is a POST so names and birth dates stay out of URLs and logs. It does not change anything.',
            ],
        ];
    }

    private function patientsShow(): array
    {
        $statuses = implode(', ', array_map(fn (PatientStatus $s) => $s->slug(), PatientStatus::cases()));

        return [
            'route' => 'api.v1.patients.show',
            'method' => 'GET',
            'path' => '/patients/{id}',
            'title' => 'Get one patient',
            'permission' => ApiKey::ABILITY_PATIENT_MATCH,
            'summary' => 'Returns a patient\'s name, birth date, status and home clinic.',
            'use_when' => 'You stored a patient id and want to confirm the link is still good, because records can be archived, merged or deleted in Open Dental.',
            'parameters_in' => 'URL path',
            'parameters' => [
                ['id', 'integer', true, 'The patient id, from a match response or a booking.'],
            ],
            'example_path' => '/patients/500',
            'example_body' => null,
            'example_response' => ['data' => $this->examplePatient()],
            'fields' => [
                ['id', 'integer', 'The patient id. Unique within one Open Dental database, but not across databases.'],
                ['status', 'text or null', "One of: {$statuses}."],
                ['clinic_num', 'integer', 'The patient\'s home clinic. 0 means none is assigned.'],
                ['birthdate, first_visit', 'date or null', 'YYYY-MM-DD. Null when blank in Open Dental.'],
            ],
            'notes' => ['Returns 404 when the id does not exist in this key\'s location. A deleted patient returns status deleted, so you can tell a stale link apart.'],
        ];
    }

    private function examplePatient(): array
    {
        return [
            'id' => 500,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'birthdate' => '1980-05-01',
            'status' => 'patient',
            'clinic_num' => 3,
            'first_visit' => '2020-01-15',
        ];
    }

    private function patientsProcedures(): array
    {
        $maxBatch = (int) config('developer_api.max_batch');
        $maxDays = (int) config('developer_api.max_procedure_range_days');

        return [
            'route' => 'api.v1.patients.procedures',
            'method' => 'POST',
            'path' => '/patients/procedures',
            'title' => 'Read completed procedures',
            'permission' => ApiKey::ABILITY_PROCEDURES,
            'summary' => 'Returns the completed procedures of up to '.$maxBatch.' patients in a date range, with each procedure\'s code, description, category and fee.',
            'use_when' => 'You need to know what care a patient has received, for example to count the benefits used in a membership year or show the value of their care. Run it nightly in batches.',
            'parameters_in' => 'JSON body',
            'parameters' => [
                ['patient_ids', 'list of integers', true, "The patients to read, at most {$maxBatch} per request, with no repeats."],
                ['start', 'date', true, 'First day to include, as YYYY-MM-DD.'],
                ['end', 'date', true, "Last day to include, as YYYY-MM-DD. The range can cover at most {$maxDays} days."],
            ],
            'example_path' => '/patients/procedures',
            'example_body' => ['patient_ids' => [500], 'start' => '2026-01-01', 'end' => '2026-12-31'],
            'example_response' => ['data' => [[
                'patient_id' => 500,
                'procedure_count' => 1,
                'total_fee' => 60,
                'procedures' => [[
                    'id' => 9001,
                    'date' => '2026-02-01',
                    'code' => 'D0120',
                    'description' => 'periodic oral evaluation',
                    'category' => 'Exams & Xrays',
                    'fee' => 60,
                    'clinic_num' => 3,
                    'provider_id' => 7,
                ]],
            ]]],
            'fields' => [
                ['procedure_count', 'integer', 'How many completed procedures fall in the range.'],
                ['total_fee', 'number', 'The sum of the fees below.'],
                ['procedures[].fee', 'number', 'The fee recorded on the procedure in Open Dental, before any adjustments or insurance.'],
                ['procedures[].code', 'text or null', 'The procedure code, such as D1110. Count benefits used from this.'],
                ['procedures[].clinic_num', 'integer', 'Where it was done. Look the name up with List clinics.'],
                ['procedures[].provider_id', 'integer or null', 'The provider who did it.'],
            ],
            'notes' => [
                'Only completed work is returned. Booked, planned and cancelled procedures never appear.',
                'Every patient you ask for is in the response, with an empty list when they have none. Replace everything you hold for that period on each read, because a procedure can be corrected or removed in Open Dental.',
                'A patient id from another location returns an empty list rather than an error, so nothing is revealed about who exists there.',
                'A clinic-limited key only sees procedures done at that clinic. To follow a patient across clinics, use a key for the whole location.',
            ],
        ];
    }

    private function procedureCodes(): array
    {
        $maxPerPage = (int) config('developer_api.max_per_page');
        $defaultPerPage = (int) config('developer_api.per_page');

        return [
            'route' => 'api.v1.procedure-codes.index',
            'method' => 'GET',
            'path' => '/procedure-codes',
            'title' => 'List procedure codes',
            'permission' => ApiKey::ABILITY_PROCEDURES,
            'summary' => 'Returns Open Dental\'s own procedure list, with each code\'s plain description and category.',
            'use_when' => 'You want to show a code\'s name without keeping your own list, or look up codes before any procedure has used them.',
            'parameters_in' => 'Query string',
            'parameters' => [
                ['codes', 'text', false, 'Only these codes, comma separated, such as D1110,D1120.'],
                ['per_page', 'integer', false, "Results per page, up to {$maxPerPage}. The default is {$defaultPerPage}."],
                ['cursor', 'text', false, 'The next page\'s position. Follow links.next instead of building this yourself.'],
            ],
            'example_path' => '/procedure-codes?codes=D0120,D1110',
            'example_body' => null,
            'example_response' => ['data' => [[
                'code' => 'D0120',
                'description' => 'periodic oral evaluation',
                'abbreviation' => 'PerEx',
                'category' => 'Exams & Xrays',
                'is_hygiene' => false,
            ]]] + $this->cursorPage('/api/v1/procedure-codes', $defaultPerPage),
            'fields' => [
                ['code', 'text', 'The procedure code.'],
                ['description', 'text or null', 'Open Dental\'s description.'],
                ['category', 'text or null', 'The category the code is filed under.'],
                ['is_hygiene', 'boolean', 'Whether Open Dental marks this as a hygiene procedure.'],
            ],
            'notes' => ['Codes belong to the Open Dental database, so a clinic-limited key sees the same list as a whole-location key.'],
        ];
    }

    private function clinics(): array
    {
        return [
            'route' => 'api.v1.clinics.index',
            'method' => 'GET',
            'path' => '/clinics',
            'title' => 'List clinics',
            'permission' => null,
            'summary' => 'Returns the name, phone number and address of each visible clinic in the key\'s location.',
            'use_when' => 'You want to turn a clinic_num into a name, or show patients how to contact a clinic.',
            'parameters_in' => null,
            'parameters' => [],
            'example_path' => '/clinics',
            'example_body' => null,
            'example_response' => ['data' => [[
                'clinic_num' => 3,
                'name' => 'Ann Arbor Main',
                'abbreviation' => 'AA',
                'phone' => '7345550100',
                'address' => ['line1' => '1 Main St', 'line2' => null, 'city' => 'Ann Arbor', 'state' => 'MI', 'zip' => '48104'],
            ]]],
            'fields' => [
                ['clinic_num', 'integer', 'The id used by bookings, patients and procedures.'],
                ['name', 'text or null', 'The clinic\'s name.'],
                ['phone', 'text or null', 'Exactly as entered in Open Dental, so format it before displaying.'],
            ],
            'notes' => [
                'A clinic-limited key returns only its own clinic.',
                'A location that does not use clinics in Open Dental returns an empty list.',
            ],
        ];
    }
}
