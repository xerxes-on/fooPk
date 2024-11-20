<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

/**
 * @deprecated
 * Page controller.
 *
 * @package App\Http\Controllers
 */
class PageController extends Controller
{
    /**
     * Show Page Pricing Table
     */
    public function showPricingTable(): Redirector|RedirectResponse
    {
        // TODO: Why page is added here?
        #$page = Models\Page::whereTranslation('slug', 'foodpunk-experience-buchen')->first();
        return redirect('https://foodpunk.com/de/preise/');
    }
    
}
