<?php

namespace App\Nexus\Modules\User\Repositories;

use App\Models\User;
use App\Nexus\Modules\User\Models\UserAddress;
use Illuminate\Database\Eloquent\Collection;

class UserAddressRepository
{
    public function getUserAddresses(User $user): Collection
    {
        return UserAddress::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_main')
            ->orderByDesc('id')
            ->get();
    }

    public function findUserAddress(User $user, int $id): ?UserAddress
    {
        return UserAddress::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();
    }
}
