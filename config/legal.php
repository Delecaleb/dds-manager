<?php

/*
|--------------------------------------------------------------------------
| Legal pages (privacy policy, terms of service)
|--------------------------------------------------------------------------
| Identity and contact details rendered on the public /privacy and /terms pages. Kept in
| config so the wording in the view never needs editing when a name changes.
*/
return [
    'company' => env('LEGAL_COMPANY', 'Detroit Dental Now'),
    'contact_email' => env('LEGAL_CONTACT_EMAIL', 'dev@detroitdentalnow.com'),
    'privacy_updated' => env('LEGAL_PRIVACY_UPDATED', '2026-10-06'),
    'terms_updated' => env('LEGAL_TERMS_UPDATED', '2026-10-06'),
];
