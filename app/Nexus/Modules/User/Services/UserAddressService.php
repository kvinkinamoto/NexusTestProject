<?php

namespace App\Nexus\Modules\User\Services;

use App\Models\User;
use App\Nexus\Modules\User\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class UserAddressService
{
    public function store(User $user, array $data): UserAddress
    {
        return DB::transaction(function () use ($user, $data) {
            $shouldBeMain = ! empty($data['is_main']) || ! $user->addresses()->exists();
            $data['is_main'] = $shouldBeMain;

            $address = $user->addresses()->create($data);

            if ($shouldBeMain) {
                $this->clearOtherMains($user, $address->id);
            }

            return $address;
        });
    }

    public function delete(UserAddress $address): void
    {
        $address->delete();
    }

    public function setMain(UserAddress $address): UserAddress
    {
        return DB::transaction(function () use ($address) {
            $this->clearOtherMains($address->user, $address->id);

            $address->is_main = true;
            $address->save();

            return $address;
        });
    }

    private function clearOtherMains(User $user, int $keepAddressId): void
    {
        $user->addresses()
            ->where('id', '!=', $keepAddressId)
            ->where('is_main', true)
            ->update(['is_main' => false]);
    }
}
