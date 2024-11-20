<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Admin\Permission\RoleEnum;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Login controller
 *
 * This controller handles authenticating users for the application and
 * redirecting them to your home screen.
 *
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    use AuthenticatesUsers {
        logout as performLogout;
    }

    public const AUTHORIZED_COOKIE = 'authorized';

    /**
     * Where to redirect users after login.
     */
    protected string $redirectTo = '/user/dashboard';

    /**
     * lockout after N attempts
     */
    protected int $maxAttempts = 3;

    /**
     * lockout for 2 minute (value is in minutes)
     */
    protected int $decayMinutes = 2;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout', 'logout.get', 'logout.post');
    }

    /**
     * Get the needed authorization credentials from the request.
     */
    protected function credentials(Request $request): array
    {
        $credentials = $request->only($this->username(), 'password');
        return Arr::add($credentials, 'status', '1');
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, mixed $user): void
    {
        \Cookie::queue(cookie()->forever(self::AUTHORIZED_COOKIE, time()));
        redirect()->intended($this->redirectPath());
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $email    = $request->input('email');
        $password = $request->input('password');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            \Cookie::queue(cookie()->forever(self::AUTHORIZED_COOKIE, time()));

            if (Auth::user()->hasRole(RoleEnum::USER->value)) {
                return redirect()->route('recipes.list');
            } else {
                return redirect()->route('admin.dashboard');
            }
        } else {
            return redirect()->back()->with('error', trans('common.auth_error'));
        }
    }

    /**
     * Log out
     */
    public function logout(Request $request): RedirectResponse
    {
        $login_route = 'login';

        if (is_null(Auth::user())) {
            $login_route = 'login.admin';
        }

        $this->performLogout($request);

        \Cookie::queue(\Cookie::forget(self::AUTHORIZED_COOKIE));
        return $this->loggedOut($request) ?: redirect()->route($login_route);
    }
}
