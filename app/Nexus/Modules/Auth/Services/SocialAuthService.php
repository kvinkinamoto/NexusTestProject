<?php

namespace App\Nexus\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialAuthService
{
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureSupported($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): User
    {
        $this->ensureSupported($provider);

        $socialUser = Socialite::driver($provider)->user();

        $user = $this->findOrCreateUser($provider, $socialUser);

        Auth::login($user, true);

        return $user;
    }

    protected function findOrCreateUser(string $provider, SocialUser $socialUser): User
    {
        $socialIdField = $provider . '_id';
        $socialId = $socialUser->getId();
        $socialEmail = $socialUser->getEmail();

        $user = User::query()
            ->where($socialIdField, $socialId)
            ->orWhere(function ($query) use ($socialEmail) {
                if ($socialEmail) {
                    $query->where('email', $socialEmail);
                }
            })
            ->first();

        if (!$user) {
            return User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
                'email' => $socialEmail,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random()),
                $socialIdField => $socialId,
            ]);
        }

        if (!$user->{$socialIdField}) {
            $user->update([$socialIdField => $socialId]);
        }

        return $user;
    }

    protected function ensureSupported(string $provider): void
    {
        $supported = config('auth.social_providers', []);

        if (!in_array($provider, $supported, true)) {
            throw ValidationException::withMessages([
                'provider' => __('auth::translate.social.unsupported_provider'),
            ]);
        }
    }
}
