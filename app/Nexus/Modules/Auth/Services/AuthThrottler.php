<?php

namespace App\Nexus\Modules\Auth\Services;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthThrottler
{
    public function keyFor(string $identifier, string $ip): string
    {
        return Str::transliterate(Str::lower($identifier) . '|' . $ip);
    }

    public function ensureNotLimited(string $throttleKey, Request $request, int $maxAttempts = 5): void
    {
        if (!RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function hit(string $throttleKey): void
    {
        RateLimiter::hit($throttleKey);
    }

    public function clear(string $throttleKey): void
    {
        RateLimiter::clear($throttleKey);
    }
}
