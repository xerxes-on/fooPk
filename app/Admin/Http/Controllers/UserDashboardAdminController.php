<?php
/**
 * @copyright   Copyright © 2019 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Andrey Rayfurak <andrey.rayfurak@lindenvalley.de>
 * @date        21.12.2019
 */

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Models\UserDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Controller for user dashboards.
 *
 * @package App\Http\Controllers\Admin
 */
final class UserDashboardAdminController extends Controller
{
    public function postEdit(Request $request): RedirectResponse
    {
        UserDashboard::findOrFail($request->route('user_dashboard'))
            ->update([
                'message'       => $request->has('message') ? $request->get('message') : null,
                'wp_article_id' => $request->has('wp_article_id') ? $request->get('wp_article_id') : null
            ]);

        return redirect()->back()->with('success_message', trans('Successfully added.'));
    }
}
