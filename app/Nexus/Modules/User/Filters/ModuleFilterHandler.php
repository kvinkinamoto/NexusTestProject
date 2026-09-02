<?php

namespace App\Nexus\Modules\User\Filters;

use Illuminate\Database\Eloquent\Builder;
//use Nodex\Nexus\Enums\AdminAvailableFilterEnum;
use Nodex\Nexus\Filters\FilterHandler;

class ModuleFilterHandler extends FilterHandler
{
    public function filter(Builder $query, string $filterName, string $value): Builder
    {
//        if ($filterName === AdminAvailableFilterEnum::FILTER_TRASHED->value) {
//            return $query;
//        }

        // Для всіх інших фільтрів викликаємо батьківський метод
        return parent::filter($query, $filterName, $value);
    }
}
