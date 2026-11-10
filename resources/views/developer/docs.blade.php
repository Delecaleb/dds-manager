@use(App\Models\ApiKey)
@php
    $section = 'bg-white p-5 sm:p-6 rounded-xl border border-slate-200 shadow-sm space-y-4 scroll-mt-4';
    $heading = 'text-base font-bold text-slate-900';
    $body = 'text-[13px] text-slate-700 leading-relaxed';
    $methodTone = ['GET' => 'bg-sky-50 text-sky-700 border-sky-200', 'POST' => 'bg-emerald-50 text-emerald-700 border-emerald-200'];
    $guides = [
        'getting-started' => 'Getting started',
        'keys-and-locations' => 'Keys and locations',
        'permissions' => 'Permissions',
        'how-data-works' => 'How the data works',
        'pagination' => 'Pagination',
        'rate-limits' => 'Rate limits',
        'errors' => 'Errors',
        'security' => 'Security',
    ];
    $membership = [
        'membership-portal' => 'In plain words',
        'membership-setup' => 'Before you start',
        'membership-linking' => 'Step 1: Find the patient',
        'membership-care' => 'Step 2: Read their treatment',
        'membership-helpers' => 'Other questions',
        'membership-boundaries' => 'What the portal does itself',
        'membership-words' => 'Words used',
    ];
    $recipes = [
        'recipe-schedule' => 'Keep a schedule up to date',
    ];
    $anchor = fn (array $endpoint) => str_replace('.', '-', substr($endpoint['route'], strlen('api.v1.')));
@endphp

<x-app-layout>
    <div class="p-4 sm:p-6 max-w-[1400px] mx-auto space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#001f3f] text-emerald-400 flex items-center justify-center shadow-inner shrink-0">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">API documentation</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Read appointments, patients and care from your synced Open Dental data.</p>
                </div>
            </div>
            <a href="{{ route('developer.index') }}"
                class="inline-flex justify-center items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-lg text-xs font-semibold">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                <span>Your API keys</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[15rem_minmax(0,1fr)] gap-6 items-start">

            <nav aria-label="Documentation sections" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto text-xs space-y-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Guides</p>
                    <ul class="space-y-1">
                        @foreach ($guides as $id => $label)
                            <li><a href="#{{ $id }}" class="text-slate-700 hover:text-emerald-700">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Membership portal</p>
                    <ul class="space-y-1">
                        @foreach ($membership as $id => $label)
                            <li><a href="#{{ $id }}" class="text-slate-700 hover:text-emerald-700">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
                @foreach ($groups as $group)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ $group['title'] }}</p>
                        <ul class="space-y-1">
                            @foreach ($group['endpoints'] as $endpoint)
                                <li><a href="#{{ $anchor($endpoint) }}" class="text-slate-700 hover:text-emerald-700">{{ $endpoint['title'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Recipes</p>
                    <ul class="space-y-1">
                        @foreach ($recipes as $id => $label)
                            <li><a href="#{{ $id }}" class="text-slate-700 hover:text-emerald-700">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </nav>

            <div class="min-w-0 space-y-6">

                <section id="getting-started" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Getting started</h2>
                    <p class="{{ $body }}">This API is read-only. Nothing you send can change Open Dental or this system. Every endpoint returns JSON, and every address starts with <code>{{ $apiBase }}</code>.</p>
                    <ol class="{{ $body }} list-decimal pl-5 space-y-1.5">
                        <li><a href="{{ route('developer.index') }}" class="text-emerald-700 font-semibold hover:underline">Create an API key</a>, choosing its location and the permissions it needs. Use <span class="font-semibold">Copy key</span> to see it.</li>
                        <li>Send the key from your server on every request, in an <code>Authorization</code> header.</li>
                        <li>Call <a href="#me" class="text-emerald-700 font-semibold hover:underline">Check your key</a> to confirm it works and reads the location you expect.</li>
                    </ol>
                    @include('developer.docs._code', ['title' => 'Your first request', 'code' => $docs->curl($docs->endpoint('api.v1.me'), $apiBase)])
                </section>

                <section id="keys-and-locations" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Keys and locations</h2>
                    <p class="{{ $body }}">A key belongs to one location, and the key alone decides what it can read. No request can ask for another location's data. To work with several Open Dental databases, create one key for each.</p>
                    <p class="{{ $body }}">For a location with several clinics, you choose when creating the key:</p>
                    <ul class="{{ $body }} list-disc pl-5 space-y-1.5">
                        <li><span class="font-semibold">All clinics:</span> reads the whole location. Use this when you follow a person between clinics, for example a membership that works at any clinic.</li>
                        <li><span class="font-semibold">One clinic:</span> reads only that clinic's bookings, patients and procedures. Care received at the location's other clinics will be missing.</li>
                    </ul>
                </section>

                <section id="permissions" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Permissions</h2>
                    <p class="{{ $body }}">Give a key only what its system needs. Permissions are set when the key is created, so a key that needs more has to be replaced by a new one. A key without the right permission gets a 403 response.</p>
                    @include('developer.docs._definitions', ['title' => 'Available permissions', 'items' => [
                        [ApiKey::ABILITY_BOOKINGS, 'every key', $abilities[ApiKey::ABILITY_BOOKINGS].'.'],
                        [ApiKey::ABILITY_PATIENT_CONTACT, 'protected health information', $abilities[ApiKey::ABILITY_PATIENT_CONTACT].'. Added to each booking.'],
                        [ApiKey::ABILITY_PATIENT_MATCH, 'protected health information', $abilities[ApiKey::ABILITY_PATIENT_MATCH].'.'],
                        [ApiKey::ABILITY_PROCEDURES, 'protected health information', $abilities[ApiKey::ABILITY_PROCEDURES].'.'],
                    ]])
                </section>

                <section id="how-data-works" class="{{ $section }}">
                    <h2 class="{{ $heading }}">How the data works</h2>
                    <ul class="{{ $body }} list-disc pl-5 space-y-1.5">
                        <li><span class="font-semibold">It is a synced copy, not live.</span> Data arrives from Open Dental on a schedule, so a change made a moment ago may not appear yet. Run nightly jobs after the night's sync has had time to finish.</li>
                        <li><span class="font-semibold">Times are local.</span> Dates and times use the office's own clock with no offset, exactly as Open Dental holds them.</li>
                        <li><span class="font-semibold">Ids are per database.</span> A patient or booking id is unique within one Open Dental database. Two locations can use the same number for different people, so store the location beside the id if you use more than one.</li>
                        <li><span class="font-semibold">Missing values are null.</span> A blank in Open Dental comes back as null, so check before you display a value.</li>
                    </ul>
                </section>

                <section id="pagination" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Pagination</h2>
                    <p class="{{ $body }}">Long lists arrive in pages. When there is more to read, <code>links.next</code> holds the full address of the next page. Request it with the same key, and stop when it is <code>null</code>. Set <code>per_page</code> to choose the page size, up to {{ $maxPerPage }}.</p>
                    <p class="{{ $body }}">Batch endpoints such as <a href="#patients-match" class="text-emerald-700 font-semibold hover:underline">Match people to patients</a> are not paged. Each request names up to {{ $maxBatch }} people or patients and gets all of their results back.</p>
                </section>

                <section id="rate-limits" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Rate limits</h2>
                    <p class="{{ $body }}">New keys can make {{ $defaultRateLimit }} requests per minute, counted per key. Each response carries <code>X-RateLimit-Limit</code> and <code>X-RateLimit-Remaining</code> headers. Going over returns a 429 response with a <code>Retry-After</code> header giving the seconds to wait.</p>
                    <p class="{{ $body }}">Use batches for regular jobs. At {{ $maxBatch }} patients per request, the default limit covers {{ number_format($defaultRateLimit * $maxBatch) }} patients a minute.</p>
                </section>

                <section id="errors" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Errors</h2>
                    <p class="{{ $body }}">Errors are JSON with a <code>message</code> explaining what went wrong. Show or log that message rather than relying on its exact wording.</p>
                    @include('developer.docs._definitions', ['title' => 'Status codes', 'items' => [
                        ['401', 'Unauthorized', 'The key is missing, wrong, expired or revoked.'],
                        ['403', 'Forbidden', 'The key is valid but lacks the permission this endpoint needs.'],
                        ['404', 'Not found', 'The booking or patient does not exist in this key\'s location.'],
                        ['422', 'Invalid request', 'A parameter is missing or wrong. The errors object names each field.'],
                        ['429', 'Too many requests', 'The rate limit was reached. Wait for the time in Retry-After, then try again.'],
                        ['500', 'Server error', 'Something failed on this system. It is safe to retry after a short wait, since every request only reads.'],
                    ]])
                    @include('developer.docs._code', ['title' => 'Example 422 response', 'code' => $docs->json(['message' => 'The start field is required.', 'errors' => ['start' => ['The start field is required.']]])])
                </section>

                <section id="security" class="{{ $section }}">
                    <h2 class="{{ $heading }}">Security</h2>
                    <ul class="{{ $body }} list-disc pl-5 space-y-1.5">
                        <li><span class="font-semibold">Keep keys on your server.</span> Never put a key in a website, mobile app or code repository. Anyone holding it can read that location's data.</li>
                        <li><span class="font-semibold">Treat responses as patient information.</span> Names, birth dates, contact details and procedures are protected health information. Encrypt them where you store them, and keep them out of your logs.</li>
                        <li><span class="font-semibold">Every request is logged.</span> The address, status, row count and IP address are recorded against the key. Administrators can review that log.</li>
                        <li><span class="font-semibold">If a key leaks,</span> regenerate or revoke it in the <a href="{{ route('developer.index') }}" class="text-emerald-700 font-semibold hover:underline">Developer Portal</a>. The old key stops working at once.</li>
                    </ul>
                </section>

                @php
                    $match = $docs->endpoint('api.v1.patients.match');
                    $procedures = $docs->endpoint('api.v1.patients.procedures');
                    $link = 'text-emerald-700 font-semibold hover:underline';
                @endphp
                <div class="pt-2">
                    <h2 class="text-lg font-bold text-slate-900">Membership portal</h2>
                    <p class="text-xs text-slate-500 mt-0.5">For a website where patients sign up for a membership plan and see what their plan has given them.</p>
                </div>

                <section id="membership-portal" class="{{ $section }}">
                    <h3 class="{{ $heading }}">In plain words</h3>
                    <p class="{{ $body }}">A membership portal is a separate website. It keeps its own list of members, plans and payments. What it does not have is the clinic's records: who the patients are and what treatment they have had. Those live in <span class="font-semibold">Open Dental</span>, the clinic's practice software.</p>
                    <p class="{{ $body }}">This API lets the portal ask two questions about those records:</p>
                    <ol class="{{ $body }} list-decimal pl-5 space-y-1.5">
                        <li><span class="font-semibold">"Which patient is this member?"</span> The portal sends a member's name and birthday, and gets back their patient number.</li>
                        <li><span class="font-semibold">"What treatment has this patient had this year?"</span> The portal sends patient numbers and gets back each completed treatment, with its name and price.</li>
                    </ol>
                    <p class="{{ $body }}">With those answers the portal can show a member which benefits they have used and how much they have saved, without anyone at the clinic typing it in. The portal can only read. Nothing it sends changes the clinic's records.</p>
                </section>

                <section id="membership-setup" class="{{ $section }}">
                    <h3 class="{{ $heading }}">Before you start: get an API key</h3>
                    <p class="{{ $body }}">An API key is a long password that the portal sends with every question. It also decides which clinic's records the portal may read.</p>
                    <ol class="{{ $body }} list-decimal pl-5 space-y-1.5">
                        <li>Go to <a href="{{ route('developer.index') }}" class="{{ $link }}">your API keys</a> and choose <span class="font-semibold">Create API key</span>.</li>
                        <li>Pick the location. If it has several clinics, pick the option ending in <span class="font-semibold">(all clinics)</span>. Members can visit any clinic, so a key for just one clinic would miss treatment done at the others.</li>
                        <li>Tick <span class="font-semibold">Patient matching</span> and <span class="font-semibold">Procedures</span>. These allow the two questions above.</li>
                        <li>Give the key to the portal's developer privately. It must be kept on the portal's server, never in a web page.</li>
                    </ol>
                    <p class="{{ $body }}">If the portal serves several locations, each with its own Open Dental, it needs one key for each.</p>
                </section>

                <section id="membership-linking" class="{{ $section }}">
                    <h3 class="{{ $heading }}">Step 1: Find the member's patient record</h3>
                    <p class="{{ $body }}">When someone becomes a member, the portal asks <span class="font-semibold">"Which patient is this?"</span> by sending their first name, last name and birthday to <a href="#patients-match" class="{{ $link }}">{{ $match['method'] }} /api/v1{{ $match['path'] }}</a>. It can also send its own member number, which comes back unchanged so the portal knows which answer is whose.</p>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @include('developer.docs._code', ['title' => 'The portal sends', 'code' => $docs->json($match['example_body'])])
                        @include('developer.docs._code', ['title' => 'It gets back', 'code' => $docs->json($match['example_response'])])
                    </div>
                    <p class="{{ $body }}">The answer for each person is one of three:</p>
                    @include('developer.docs._definitions', ['title' => 'The three possible answers', 'items' => [
                        ['matched', 'found', 'One patient fits. Save their patient number (patient.id) with the member. That number is what Step 2 uses.'],
                        ['none', 'not found yet', 'No patient fits. They are probably new and have not had a first visit. Ask again each night until they are found.'],
                        ['multiple', 'needs a person', 'More than one patient fits. Show the choices (candidates) to clinic staff and let them pick. Never pick one automatically.'],
                    ]])
                    <p class="{{ $body }}">The name must match exactly as the clinic recorded it. "Bob" will not find "Robert". If a member is still not found after they should have visited, show them to staff to check the spelling.</p>
                    <p class="{{ $body }}">Each person in a family is looked up on their own, children included, because each has their own patient record.</p>
                </section>

                <section id="membership-care" class="{{ $section }}">
                    <h3 class="{{ $heading }}">Step 2: Read their treatment each night</h3>
                    <p class="{{ $body }}">Once a night, the portal asks <span class="font-semibold">"What treatment have these patients had?"</span> by sending up to {{ $maxBatch }} patient numbers and the dates of the membership year to <a href="#patients-procedures" class="{{ $link }}">{{ $procedures['method'] }} /api/v1{{ $procedures['path'] }}</a>.</p>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @include('developer.docs._code', ['title' => 'The portal sends', 'code' => $docs->json($procedures['example_body'])])
                        @include('developer.docs._code', ['title' => 'It gets back', 'code' => $docs->json($procedures['example_response'])])
                    </div>
                    <p class="{{ $body }}">Only treatment that was actually done comes back. Booked or cancelled appointments never count. Each item has a code (such as D1110), its name, the date and the price the clinic charged.</p>
                    @include('developer.docs._definitions', ['title' => 'What the portal can then show', 'items' => [
                        ['Benefits used', 'by code', 'Count the codes each benefit covers. For example, if the plan includes one cleaning, a D1110 or D1120 means it has been used. The plan decides which codes count.'],
                        ['Value of care', 'total_fee', 'What the year\'s treatment would normally cost: the sum of its prices.'],
                        ['What the member paid', 'the portal knows', 'The membership fee and anything else the portal has recorded. This API does not know about payments.'],
                        ['Saved', 'subtract', 'Value of care minus what the member paid.'],
                    ]])
                    <p class="{{ $body }}">Each night, throw away last night's list for that year and keep the new one. The clinic sometimes corrects or removes a treatment, and the new list always has the right answer.</p>
                    <p class="{{ $body }}">The price is the clinic's normal charge before any discount or insurance. If the plan gives a discount on some treatments, for example specialty care, the portal has to work that out itself for now.</p>
                </section>

                <section id="membership-helpers" class="{{ $section }}">
                    <h3 class="{{ $heading }}">Other things the portal can ask</h3>
                    @include('developer.docs._definitions', ['title' => 'Optional helpers', 'items' => collect($docs->membershipNeeds())
                        ->reject(fn ($need) => in_array($need['endpoint']['route'], ['api.v1.patients.match', 'api.v1.patients.procedures'], true))
                        ->map(fn ($need) => [$need['endpoint']['method'].' /api/v1'.$need['endpoint']['path'], $need['endpoint']['title'], $need['need'].'.'])
                        ->values()->all()])
                </section>

                <section id="membership-boundaries" class="{{ $section }}">
                    <h3 class="{{ $heading }}">What the portal does itself</h3>
                    <p class="{{ $body }}">This API only answers questions about the clinic's records. Everything else belongs to the portal: sign-up and the signed agreement, payments and receipts, whether a membership is active, renewals and reminders, family groups, member numbers and reports. Members book appointments through the clinic's existing booking page.</p>
                    <p class="{{ $body }}">Names, birthdays and treatment are private health information. The portal must protect them, as described under <a href="#security" class="{{ $link }}">Security</a>.</p>
                </section>

                <section id="membership-words" class="{{ $section }}">
                    <h3 class="{{ $heading }}">Words used on this page</h3>
                    @include('developer.docs._definitions', ['title' => 'Glossary', 'items' => [
                        ['API', 'term', 'A way for one system to ask another system questions over the internet.'],
                        ['Endpoint', 'term', 'One kind of question, at its own web address, such as /api/v1/patients/match.'],
                        ['API key', 'term', 'The password sent with every question. It decides which location can be read.'],
                        ['Permission', 'term', 'Which questions a key is allowed to ask, ticked when the key is created.'],
                        ['Patient number', 'patient.id', 'Open Dental\'s number for a patient. Unique within one location\'s Open Dental.'],
                        ['Procedure', 'term', 'One piece of treatment, such as an exam or a cleaning.'],
                        ['Procedure code', 'code', 'The standard short code for a treatment, such as D1110 for an adult cleaning.'],
                        ['Batch', 'term', 'Several members or patients sent in one question, up to '.$maxBatch.'.'],
                        ['Synced copy', 'term', 'This system copies Open Dental\'s records on a schedule, so a change made in the clinic a moment ago may not show yet.'],
                    ]])
                </section>

                @foreach ($groups as $group)
                    <div class="pt-2">
                        <h2 class="text-lg font-bold text-slate-900">{{ $group['title'] }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $group['description'] }}</p>
                    </div>

                    @foreach ($group['endpoints'] as $endpoint)
                        <section id="{{ $anchor($endpoint) }}" class="{{ $section }}">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h3 class="{{ $heading }}">{{ $endpoint['title'] }}</h3>
                                @if ($endpoint['permission'])
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-semibold">Needs {{ $endpoint['permission'] }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-semibold">Any valid key</span>
                                @endif
                            </div>

                            <p class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded border text-[11px] font-bold {{ $methodTone[$endpoint['method']] }}">{{ $endpoint['method'] }}</span>
                                <code class="text-[13px] font-semibold text-slate-900 break-all">/api/v1{{ $endpoint['path'] }}</code>
                            </p>

                            <p class="{{ $body }}">{{ $endpoint['summary'] }}</p>
                            <p class="{{ $body }}"><span class="font-semibold text-slate-900">When to use it:</span> {{ $endpoint['use_when'] }}</p>

                            @if ($endpoint['parameters'] !== [])
                                @include('developer.docs._definitions', ['title' => 'Parameters ('.$endpoint['parameters_in'].')', 'items' => $endpoint['parameters']])
                            @endif

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                @include('developer.docs._code', ['title' => 'Example request', 'code' => $docs->curl($endpoint, $apiBase)])
                                @include('developer.docs._code', ['title' => 'Example response', 'code' => $docs->json($endpoint['example_response'])])
                            </div>

                            @include('developer.docs._definitions', ['title' => 'Response fields worth knowing', 'items' => $endpoint['fields']])

                            @if ($endpoint['notes'] !== [])
                                <div>
                                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Good to know</h4>
                                    <ul class="{{ $body }} list-disc pl-5 space-y-1.5">
                                        @foreach ($endpoint['notes'] as $note)
                                            <li>{{ $note }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </section>
                    @endforeach
                @endforeach

                <div class="pt-2">
                    <h2 class="text-lg font-bold text-slate-900">Recipes</h2>
                    <p class="text-xs text-slate-500 mt-0.5">How the endpoints fit together for other common jobs.</p>
                </div>

                <section id="recipe-schedule" class="{{ $section }}">
                    <h3 class="{{ $heading }}">Keep a schedule up to date</h3>
                    <p class="{{ $body }}">For a system that holds its own copy of the appointment book.</p>
                    <ol class="{{ $body }} list-decimal pl-5 space-y-1.5">
                        <li>Call <a href="#bookings-index" class="text-emerald-700 font-semibold hover:underline">List bookings</a> for the dates you care about, following <code>links.next</code> until it is null.</li>
                        <li>Keep the highest <code>updated_at</code> you received.</li>
                        <li>On each later check, send that time as <code>updated_since</code> and apply only what comes back.</li>
                        <li>Re-read the full date range once a day and remove anything that no longer appears, because deleted bookings are not reported as changes.</li>
                    </ol>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
