<?php

namespace App\Nexus\Modules\User\Services;

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Contracts\UrlResolverInterface;

class UserMenuResolver implements UrlResolverInterface
{
    public function resolve(Model $model): ?string
    {
        try {
            if (! app('router')->has('user.profile')) {
                return null;
            }

            return route('user.profile', ['id' => $model->id], false);
        } catch (\Throwable) {
            return null;
        }
    }
}
