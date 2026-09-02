<?php

namespace App\Nexus\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Auth\Requests\ResetPasswordRequest;
use App\Nexus\Modules\Auth\Requests\SendPasswordResetLinkRequest;
use App\Nexus\Modules\Auth\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(protected PasswordResetService $passwordResetService)
    {
    }

    public function sendResetLink(SendPasswordResetLinkRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->passwordResetService->sendResetLink($data['email'], $request);

        return response()->json(['success' => true]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth::reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->reset($request->validated());

        return response()->json(['success' => true]);
    }
}
