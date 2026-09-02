<?php

namespace App\Nexus\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuthThrottler $throttler,
    ) {
    }

    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

//         $user->sendEmailVerificationNotification();

        Auth::login($user);

        return $user;
    }

    public function login(array $data, Request $request): void
    {
        $throttleKey = $this->throttler->keyFor($data['email'], $request->ip());
        $this->throttler->ensureNotLimited($throttleKey, $request);

        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
        $remember = (bool) ($data['remember'] ?? false);

        if (!Auth::attempt($credentials, $remember)) {
            $this->throttler->hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth::translate.incorrect_credential'),
            ]);
        }

        $this->throttler->clear($throttleKey);
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
