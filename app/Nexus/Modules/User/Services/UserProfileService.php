<?php

namespace App\Nexus\Modules\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileService
{
    public function updateProfile(User $user, array $data): User
    {
        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;

        $user->fill($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $user->save();
    }
}
