<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The Growth Engine shell: the marketing module runs as its own app inside DDS Manager,
 * with a persistent left nav of its own instead of the analytics overlay menu.
 */
class MarketingLayout extends Component
{
    public function render(): View
    {
        return view('layouts.marketing');
    }
}
