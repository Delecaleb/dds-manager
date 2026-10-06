@extends('layouts.legal')

@section('title', 'Privacy Policy')
@section('description', 'How ' . config('app.name') . ' collects, uses, and protects data, including Google Ads data and website analytics.')

@php
    $appName = config('app.name');
    $company = config('legal.company');
    $email = config('legal.contact_email');
    $support = config('legal.support_email');
    $updated = \Carbon\Carbon::parse(config('legal.privacy_updated'))->format('F j, Y');
@endphp

@section('content')
        <article class="legal bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 px-6 sm:px-10 py-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Privacy Policy</h1>
            <p class="mt-2 text-sm text-slate-500">Last updated {{ $updated }}</p>

            <p class="mt-6">
                {{ $appName }} (the "Application") is an internal practice-analytics platform operated by
                <strong>{{ $company }}</strong> ("we", "us", "our") for the dental offices we own and manage.
                This policy explains what information the Application handles, where it comes from, how it is used,
                and the choices available to the people it relates to. The Application is used only by our employees
                and contractors. It is not offered to the public and we do not sell any data.
            </p>

            <h2>1. Who this policy covers</h2>
            <ul>
                <li><strong>Authorized users</strong>: our staff who sign in to the Application.</li>
                <li><strong>Patients</strong> of our dental offices, whose records are synchronized from our practice management system.</li>
                <li><strong>Website visitors</strong> to websites we own, where our first-party analytics snippet is installed.</li>
                <li><strong>Google Ads account holders</strong> who connect a Google account so the Application can read advertising reports.</li>
            </ul>

            <h2>2. Information we collect</h2>

            <h3>2.1 Account information</h3>
            <p>
                When an authorized user is given access we store their name, work email address, a hashed password,
                role and office assignments, and sign-in activity such as timestamps and IP addresses for security auditing.
            </p>

            <h3>2.2 Practice management data</h3>
            <p>
                The Application synchronizes data from our practice management system (Open Dental) into a local
                reporting database. This includes patient demographics, appointments, procedures, treatment plans,
                insurance claims, payments, adjustments, balances, recall records, and provider schedules. This data is
                already held by our offices as part of providing dental care. The Application reads it and never writes
                back to the practice management system, with the single exception of features that are explicitly
                designed to schedule or confirm appointments on a patient's behalf.
            </p>
            <p>
                Patient data may include protected health information (PHI) under the Health Insurance Portability and
                Accountability Act (HIPAA). We handle it in accordance with our HIPAA policies and our obligations as a
                covered entity. Access is restricted to staff with a legitimate business need.
            </p>

            <h3>2.3 Google Ads data</h3>
            <p>
                An authorized user may connect a Google account using Google's OAuth sign-in. The Application requests the
                Google Ads scope (<code>https://www.googleapis.com/auth/adwords</code>)
                and uses it <strong>only to read</strong> advertising reports for Google Ads accounts we own or manage.
                Specifically we retrieve:
            </p>
            <ul>
                <li>the list of Google Ads customer accounts accessible to the connected account, with their names, IDs, currency and time zone;</li>
                <li>campaign names, statuses and channel types;</li>
                <li>daily campaign performance metrics such as cost, impressions, clicks and conversions.</li>
            </ul>
            <p>
                We also store the OAuth refresh token Google issues so that the sync can run on a schedule without the
                user signing in again. Tokens are stored encrypted at rest. The Application does not create, edit or
                delete campaigns, ads, budgets, bids, keywords or any other Google Ads settings, and it does not read
                Gmail, Drive, Contacts or any other Google product.
            </p>

            <h3>2.4 Website analytics</h3>
            <p>
                Websites we own may include a small JavaScript snippet served by the Application. When a page is viewed
                or a form is submitted, the snippet sends us the page URL, the referring URL, campaign parameters in the
                URL (such as utm_source, utm_medium, utm_campaign, gclid and fbclid), the browser's user-agent string, the
                event type, and a randomly generated visitor identifier and session identifier. The identifiers are
                stored in the browser's local storage, not in cookies, and are not shared with any third party. If a
                visitor submits a contact or appointment form, the details they enter (such as name, phone number and
                email) may be associated with their visit so we can measure which marketing channels produce new patients.
            </p>

            <h3>2.5 Technical logs</h3>
            <p>
                Like most web applications we keep server logs, error logs and synchronization logs that may include IP
                addresses, request paths and timestamps. These are used for security, troubleshooting and reliability.
            </p>

            <h2>3. How we use information</h2>
            <ul>
                <li>To produce dashboards and reports about production, collections, scheduling, treatment acceptance, recall and patient flow across our offices.</li>
                <li>To measure the return on our advertising spend by comparing Google Ads costs and conversions with new-patient and production figures from our practice management system.</li>
                <li>To attribute website visits and form submissions to the marketing channel that produced them.</li>
                <li>To authenticate users, enforce role-based access and keep an audit trail.</li>
                <li>To maintain, secure and improve the Application.</li>
            </ul>
            <p>We do not use any of this information for advertising to patients, for profiling unrelated to our own operations, or for training machine-learning models.</p>

            <h2>4. Google API Services User Data Policy</h2>
            <p>
                The Application's use and transfer of information received from Google APIs adheres to the
                <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener">Google API Services User Data Policy</a>,
                including the Limited Use requirements. In particular:
            </p>
            <ul>
                <li>Google user data is used only to provide the advertising reporting features described above.</li>
                <li>We do not transfer Google user data to third parties except as necessary to provide those features, to comply with law, or as part of a merger or acquisition with notice.</li>
                <li>We do not use Google user data to serve advertisements.</li>
                <li>Humans do not read Google user data except with the account holder's consent, for security purposes, to comply with law, or when the data is aggregated and anonymized for internal operations.</li>
            </ul>
            <p>
                A connected Google account can be disconnected at any time from the Application's integrations page, or by
                revoking access at <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">myaccount.google.com/permissions</a>.
                When access is revoked we delete the stored token and stop synchronizing. Previously synchronized
                campaign statistics are retained as part of our historical marketing records unless deletion is requested.
            </p>

            <h2>5. How we share information</h2>
            <p>We do not sell, rent or trade any information. We share information only:</p>
            <ul>
                <li><strong>With service providers</strong> who host the Application and its database, or who provide email, error monitoring or backup services, under agreements that restrict their use of the data (and Business Associate Agreements where PHI is involved).</li>
                <li><strong>With Google</strong>, to the extent that calling the Google Ads API necessarily transmits our account identifiers and OAuth credentials to Google.</li>
                <li><strong>When required by law</strong>, such as to respond to a subpoena, court order or lawful request by a public authority.</li>
                <li><strong>To protect rights and safety</strong>, including enforcing our policies and investigating fraud or security incidents.</li>
            </ul>

            <h2>6. Data retention</h2>
            <ul>
                <li>Practice management data is retained for as long as our offices are required to keep patient records under applicable law.</li>
                <li>Google Ads campaign statistics are retained as historical marketing data for as long as the Application is in operation.</li>
                <li>OAuth tokens are deleted when a connection is removed or access is revoked.</li>
                <li>Website analytics data is retained for up to 36 months, after which it is deleted or aggregated.</li>
                <li>Server and security logs are retained for up to 12 months.</li>
                <li>User accounts are deactivated when employment ends and their records are removed in line with our internal retention schedule.</li>
            </ul>

            <h2>7. Security</h2>
            <p>
                The Application is accessible only to authenticated users over HTTPS. Access is governed by roles and
                per-office permissions. Credentials and API tokens are encrypted at rest. The reporting database is
                hosted in an access-controlled environment with regular backups. No method of transmission or storage
                is completely secure, but we work to protect information using administrative, technical and physical
                safeguards appropriate to its sensitivity.
            </p>

            <h2>8. Your rights and choices</h2>
            <p>
                <strong>Patients</strong> have rights regarding their health information under HIPAA, including the right to
                access and request amendment of their records. Those requests should be directed to the dental office
                that provided care, which will coordinate with us as needed.
            </p>
            <p>
                <strong>Website visitors</strong> can clear the visitor identifier by clearing their browser's site data, and
                can block the snippet with a content blocker. We honor requests to delete analytics data associated with
                a form submission.
            </p>
            <p>
                <strong>Google account holders</strong> can disconnect or revoke access as described in Section 4 and can
                request deletion of any data derived from their account.
            </p>
            <p>
                Depending on where you live you may have additional rights, such as the right to know what personal
                information we hold about you, to request its deletion, or to object to certain processing. To exercise
                any right, contact us using the details below. We will respond within the time required by applicable law.
            </p>

            <h2>9. Children</h2>
            <p>
                The Application is not directed to children and we do not knowingly collect information from children
                through it, other than patient records that a parent or guardian has provided to one of our dental
                offices in the course of care.
            </p>

            <h2>10. Changes to this policy</h2>
            <p>
                We may update this policy from time to time. The date at the top shows when it was last revised. Material
                changes will be communicated to authorized users within the Application.
            </p>

            <h2>11. Contact</h2>
            <p>
                Questions, requests or complaints about this policy or our handling of information can be sent to:
            </p>
            <p>
                <strong>{{ $company }}</strong><br>
                Privacy and legal: <a href="mailto:{{ $email }}">{{ $email }}</a><br>
                Technical support: <a href="mailto:{{ $support }}">{{ $support }}</a>
            </p>
        </article>
@endsection
