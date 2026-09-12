<?php

namespace App\Nexus\Modules\ShopProduct\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ShopProduct\Http\Resources\ProductSuggestionResource;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Live search-as-you-type suggestions for the header search box — a
     * small, fast subset of what /catalog?q= already does server-side, kept
     * separate so it stays a cheap JSON call instead of re-rendering the
     * whole catalog page fragment on every keystroke.
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['products' => []]);
        }

        $products = ShopProduct::query()
            ->publish()
            ->with('category')
            ->where('name', 'like', '%'.$q.'%')
            ->take(6)
            ->get();

        return response()->json([
            'products' => ProductSuggestionResource::collection($products),
            'more_url' => route('shop.catalog', ['q' => $q]),
        ]);
    }
}
