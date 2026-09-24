<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Growth Engine — the marketing module's page shell.
 *
 * Template stage: every action renders its page structure only. Numbers will come from
 * domain services under App\Domain\Marketing (never from this controller), exactly as the
 * analytics modules take theirs from their own domain services.
 */
class MarketingController extends Controller
{
    public function index(): View
    {
        return view('marketing.overview');
    }

    public function funnel(): View
    {
        return view('marketing.funnel');
    }

    public function channels(): View
    {
        return view('marketing.channels');
    }

    public function campaigns(): View
    {
        return view('marketing.campaigns');
    }

    public function leads(): View
    {
        return view('marketing.leads');
    }

    public function automations(): View
    {
        return view('marketing.automations');
    }

    public function alerts(): View
    {
        return view('marketing.alerts');
    }

    public function integrations(): View
    {
        return view('marketing.integrations');
    }

    public function settings(): View
    {
        return view('marketing.settings');
    }
}
