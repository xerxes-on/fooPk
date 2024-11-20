<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Admin\Permission\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Auth;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Login controller for admins.
 *
 * @package App\Http\Controllers\Auth
 */
class AdminLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * lockout after N attempts
     */
    protected int $maxAttempts = 3;

    /**
     * lockout for 2 minute (value is in minutes)
     */
    protected int $decayMinutes = 2;

    public function __construct()
    {
        $this->middleware('check.adminServer')->only(['login', 'showLoginForm']);
        $this->middleware('guest')->except(['logout', 'logout.get', 'logout.post']);
    }

    public function showLoginForm(): Factory|View
    {
        return view('auth.admin_login');
    }

    public function login(Request $request): RedirectResponse
    {
        Auth::shouldUse('admin');
        // Prevent login if admin attempt is inactive or missing
        if (!Admin::whereEmail($request->email)->first(['status'])?->status) {
            return redirect()->back()->withErrors(['status' => trans('auth.failed_on_status')]);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // login redirect to admin/users/ or admin/dashboard
            return Auth::user()?->hasPermissionTo(PermissionEnum::SEE_ALL_CLIENTS->value) ?
                redirect()->route('admin.model', ['adminModel' => 'users']) :
                redirect()->route('admin.dashboard');
        }

        return redirect()->back()->withErrors(['status' => trans('auth.failed')])->withInput($request->only('email', 'remember'));
    }
}
