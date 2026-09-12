<?php

namespace App\Nexus\Modules\Cart\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\View\View;

class CartPageController extends Controller
{
    /**
     * Renders the page shell only — line items/totals are loaded client-side
     * from the existing GET /api/cart/ endpoint (see resources/js/storefront.js's
     * `cartPage` Alpine component), which now resolves a guest's cart by
     * session id the same way it resolves a logged-in user's by user_id.
     */
    public function index(): View
    {
        return view('cart::public.cart', [
            'suggested' => ShopProduct::query()->publish()->with('category')->inRandomOrder()->take(5)->get(),
        ]);
    }
}
