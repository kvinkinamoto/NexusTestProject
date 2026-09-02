<?php

namespace App\Nexus\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Auth\Requests\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): JsonResponse
    {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail(),
            'message' => __('auth::translate.email_verification.not_verified'),
        ], 403);
    }

    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['success' => true, 'already_verified' => true]);
        }

        $request->fulfill();

        return response()->json(['success' => true]);
    }

    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['success' => false, 'already_verified' => true]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['success' => true]);
    }
}
