<?php

namespace App\Nexus\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function __construct(
        protected AuthThrottler $throttler,
    ) {
    }

    public function sendResetLink(string $email, Request $request): void
    {
        $throttleKey = $this->throttler->keyFor('reset|' . $email, $request->ip());
        $this->throttler->ensureNotLimited($throttleKey, $request, 3);

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            $this->throttler->hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        $this->throttler->clear($throttleKey);
    }

    public function reset(array $data): void
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }
}
