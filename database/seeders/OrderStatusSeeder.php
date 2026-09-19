<?php

namespace Database\Seeders;

use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use Illuminate\Database\Seeder;

/**
 * OrderObserver reacts to the exact slugs 'confirmed'/'delivered'/'cancelled'
 * (stock reduction/restore, purchase bonuses) and OrderService looks up the
 * is_default one for every new order — checkout throws without at least
 * that row. Nothing seeds these out of the box; every admin CRUD test in
 * this suite creates its own throwaway rows instead, but a real environment
 * needs real ones that stick.
 */
class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Нове', 'name_en' => 'New', 'slug' => 'new', 'is_default' => true],
            ['name' => 'Підтверджено', 'name_en' => 'Confirmed', 'slug' => 'confirmed', 'is_default' => false],
            ['name' => 'Виконано', 'name_en' => 'Delivered', 'slug' => 'delivered', 'is_default' => false],
            ['name' => 'Скасовано', 'name_en' => 'Cancelled', 'slug' => 'cancelled', 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            OrderStatus::query()->updateOrCreate(
                ['slug' => $status['slug']],
                [
                    'name' => ['uk' => $status['name'], 'en' => $status['name_en']],
                    'is_default' => $status['is_default'],
                    'publish' => true,
                ],
            );
        }
    }
}
