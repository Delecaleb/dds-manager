@extends('layouts.legal')

@section('title', 'Terms of Service')
@section('description', 'Terms governing use of ' . config('app.name') . ', an internal dental practice-analytics platform.')

@php
    $appName = config('app.name');
    $company = config('legal.company');
    $email = config('legal.contact_email');
    $updated = \Carbon\Carbon::parse(config('legal.terms_updated'))->format('F j, Y');
@endphp

@section('content')
        <article class="legal bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 px-6 sm:px-10 py-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Terms of Service</h1>
            <p class="mt-2 text-sm text-slate-500">Last updated {{ $updated }}</p>

            <p class="mt-6">
                These Terms of Service ("Terms") govern access to and use of {{ $appName }} (the "Application"), an
                internal practice-analytics platform operated by <strong>{{ $company }}</strong> ("we", "us", "our").
                By signing in to the Application, connecting a third-party account to it, or otherwise using it, you
                agree to these Terms. If you do not agree, do not use the Application.
            </p>

            <h2>1. Who may use the Application</h2>
            <p>
                The Application is provided solely to employees, contractors and authorized agents of {{ $company }}
                and its affiliated dental offices ("Authorized Users"). It is not offered to the public, to patients, or
                to any other business. Access is granted by an administrator and may be limited by role and office.
                You may use the Application only for the business purposes of {{ $company }} and only within the
                permissions assigned to you.
            </p>

            <h2>2. Accounts and security</h2>
            <ul>
                <li>You are responsible for keeping your credentials confidential and for all activity under your account.</li>
                <li>Accounts are personal. Do not share your login or let anyone else act under it.</li>
                <li>Notify us immediately at <a href="mailto:{{ $email }}">{{ $email }}</a> if you suspect unauthorized access.</li>
                <li>We may suspend or revoke access at any time, including when employment or engagement ends.</li>
            </ul>

            <h2>3. Protected health information</h2>
            <p>
                The Application displays information synchronized from our practice management system, which includes
                protected health information (PHI) under the Health Insurance Portability and Accountability Act (HIPAA).
                As an Authorized User you must:
            </p>
            <ul>
                <li>access PHI only when needed to perform your job duties (the "minimum necessary" standard);</li>
                <li>follow {{ $company }}'s HIPAA policies, privacy training and workforce sanctions policy;</li>
                <li>not download, export, screenshot, print or transmit PHI outside approved systems and workflows;</li>
                <li>report any suspected privacy or security incident without delay.</li>
            </ul>
            <p>Misuse of PHI may result in loss of access, disciplinary action and legal liability.</p>

            <h2>4. Acceptable use</h2>
            <p>You agree not to:</p>
            <ul>
                <li>use the Application for any purpose other than the operations of {{ $company }};</li>
                <li>attempt to bypass authentication, role restrictions or office permissions;</li>
                <li>probe, scan, overload or interfere with the Application or its infrastructure;</li>
                <li>scrape, bulk-export or mirror data except through features built for that purpose;</li>
                <li>introduce malicious code or use automated tools to access the Application without written approval;</li>
                <li>reverse engineer, copy or redistribute the Application's software.</li>
            </ul>

            <h2>5. Third-party integrations</h2>
            <p>
                The Application can connect to third-party services on our behalf, including Google Ads for read-only
                advertising reports and Open Dental as our practice management system. By connecting an account you
                confirm that you are authorized by {{ $company }} to do so and that the account belongs to, or is
                managed on behalf of, {{ $company }}.
            </p>
            <p>
                Use of Google services through the Application is also subject to the
                <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Google Terms of Service</a>,
                the <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy</a>
                and the <a href="https://ads.google.com/intl/en_us/home/terms/" target="_blank" rel="noopener">Google Ads Terms and Conditions</a>.
                You may disconnect a Google account at any time from the Application's integrations page or at
                <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">myaccount.google.com/permissions</a>.
            </p>
            <p>
                We are not responsible for the availability, accuracy or conduct of third-party services, and features that
                depend on them may change or stop working if those services change.
            </p>

            <h2>6. Data and privacy</h2>
            <p>
                Our <a href="{{ route('privacy') }}">Privacy Policy</a> describes what information the Application
                collects and how it is used, shared and retained. It forms part of these Terms.
            </p>

            <h2>7. Accuracy of reports</h2>
            <p>
                The Application produces analytics derived from synchronized data. Figures may lag the source system,
                may be restated as upstream data changes (for example when Google Ads revises conversion counts or when
                claims are adjusted), and depend on the correctness of data entered in the practice management system.
                Reports are provided to support business decisions and are not a substitute for the official records in
                the practice management system, accounting system or payer statements. Always verify material figures
                against the system of record before relying on them.
            </p>

            <h2>8. Intellectual property</h2>
            <p>
                The Application, including its software, design, dashboards, metric definitions and documentation, is
                owned by {{ $company }} or its licensors and is protected by applicable intellectual property laws.
                These Terms grant you a limited, revocable, non-transferable right to use the Application for its
                intended purpose and no other rights.
            </p>

            <h2>9. Availability and changes</h2>
            <p>
                We aim to keep the Application available but do not guarantee uninterrupted service. We may modify,
                suspend or discontinue any feature at any time, perform maintenance, or change these Terms. The date
                at the top shows when the Terms were last revised. Continued use after a change constitutes acceptance
                of the revised Terms.
            </p>

            <h2>10. Disclaimer of warranties</h2>
            <p>
                The Application is provided "as is" and "as available" without warranties of any kind, whether express
                or implied, including implied warranties of merchantability, fitness for a particular purpose, accuracy
                and non-infringement, to the fullest extent permitted by law.
            </p>

            <h2>11. Limitation of liability</h2>
            <p>
                To the fullest extent permitted by law, {{ $company }} and its officers, employees and agents will not be
                liable for any indirect, incidental, special, consequential or punitive damages, or for any loss of
                profits, revenue, data or goodwill, arising out of or related to use of the Application. Nothing in
                these Terms limits liability that cannot be limited under applicable law, or alters obligations that
                exist under an employment agreement, contractor agreement, Business Associate Agreement or HIPAA.
            </p>

            <h2>12. Termination</h2>
            <p>
                We may suspend or terminate your access at any time, with or without notice, including for breach of
                these Terms or when your relationship with {{ $company }} ends. Sections 3, 6, 8, 10, 11 and 13 survive
                termination.
            </p>

            <h2>13. Governing law</h2>
            <p>
                These Terms are governed by the laws of the State of Michigan and the United States, without regard to
                conflict-of-law principles. Any dispute will be resolved in the state or federal courts located in Wayne
                County, Michigan, unless an applicable employment or contractor agreement provides otherwise.
            </p>

            <h2>14. Contact</h2>
            <p>Questions about these Terms can be sent to:</p>
            <p>
                <strong>{{ $company }}</strong><br>
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>
        </article>
@endsection
