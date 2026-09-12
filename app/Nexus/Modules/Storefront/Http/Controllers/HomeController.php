<?php

namespace App\Nexus\Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $newArrivals = ShopProduct::query()->publish()->with('category')->latest()->take(10)->get();

        // No bestseller/order-count metric exists anywhere in the app yet
        // (confirmed during planning) — random selection is a placeholder
        // until real popularity tracking is added.
        $popularProducts = ShopProduct::query()->publish()->with('category')->inRandomOrder()->take(10)->get();

        $categories = ShopCategory::query()->publish()->whereIsRoot()->orderBy('name')->take(4)->get();

        return view('storefront::public.home', [
            'newArrivals' => $newArrivals,
            'popularProducts' => $popularProducts,
            'categories' => $categories,
        ]);
    }
}
