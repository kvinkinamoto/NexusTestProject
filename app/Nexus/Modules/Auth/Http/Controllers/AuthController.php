<?php

namespace App\Nexus\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Auth\Http\Resources\UserResource;
use App\Nexus\Modules\Auth\Requests\LoginRequest;
use App\Nexus\Modules\Auth\Requests\RegisterUserRequest;
use App\Nexus\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function loginPage(Request $request): View
    {
        return view('auth::login');
    }

    /**
     * There is no GET /register route with a JSON-only counterpart anywhere
     * else — this renders the form, which submits via fetch() from the
     * view's own script (see auth::register).
     */
    public function registerPage(): View
    {
        return view('auth::register');
    }

    public function forgotPasswordPage(): View
    {
        return view('auth::forgot-password');
    }

    /**
     * One login form, one auth path, regardless of whether the visitor was
     * headed for the storefront or /admin — the `auth` middleware already
     * records where they were trying to go before bouncing them here, so
     * intended() sends them back there. No separate "admin login" exists
     * anymore; whether /admin/{module} lets them in past this point is
     * NexusAdminMiddleware's permission check, not a routing decision.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $this->authService->login($request->validated(), $request);

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ]);
    }

    public function apiLogin(LoginRequest $request): JsonResponse
    {
        $this->authService->login($request->validated(), $request);

        $request->session()->regenerate();
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * One logout path, same reasoning as login(): whether the visitor came
     * from the storefront or /admin isn't this route's concern. Sends
     * everyone back to the login page rather than back into /admin, which
     * would just immediately bounce them to /login anyway via the auth
     * middleware once there's no session left to satisfy it.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function apiLogout(Request $request): JsonResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
