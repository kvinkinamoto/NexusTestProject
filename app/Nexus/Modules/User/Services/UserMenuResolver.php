<?php

namespace App\Nexus\Modules\User\Services;

use App\Nexus\Modules\Menu\Contracts\MenuUrlResolverInterface;
use Illuminate\Database\Eloquent\Model;

class UserMenuResolver implements MenuUrlResolverInterface
{
    public function resolve(Model $model): ?string
    {
        try {
            if (!app('router')->has('user.profile')) {
                return null;
            }
            return route('user.profile', ['id' => $model->id], false);
        } catch (\Throwable) {
            return null;
        }
    }
}
