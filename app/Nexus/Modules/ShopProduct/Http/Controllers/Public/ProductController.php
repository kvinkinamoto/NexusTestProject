<?php

namespace App\Nexus\Modules\ShopProduct\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductReview\Services\ReviewService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, ShopProduct $product, ReviewService $reviewService): View
    {
        if (! $product->publish) {
            abort(404);
        }

        $product->load(['category', 'attributeOptions.attribute', 'variationGroup.products.attributeOptions.attribute']);

        $pricing = ResolveProductPriceAction::handle($product, 1);

        $variationSiblings = $product->variationGroup
            ? $product->variationGroup->products
            : collect([$product]);

        $reviews = $reviewService->getReviewsById($product->id, 10, (int) $request->query('page', 1));
        $averageRating = $reviewService->getAverageRating($product->id);

        $relatedProducts = $product->relatedProducts()->publish()->with('category')->take(4)->get();
        if ($relatedProducts->isEmpty() && $product->category_id) {
            $relatedProducts = ShopProduct::query()
                ->publish()
                ->with('category')
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get();
        }

        $releases = $product->releases()->published()->get();

        return view('shopProduct::public.product', [
            'product' => $product,
            'price' => $pricing['price'],
            'originalPrice' => $pricing['original_price'],
            'variationSiblings' => $variationSiblings,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'relatedProducts' => $relatedProducts,
            'releases' => $releases,
        ]);
    }
}
