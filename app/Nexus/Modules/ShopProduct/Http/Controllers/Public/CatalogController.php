<?php

namespace App\Nexus\Modules\ShopProduct\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Currency\Services\CurrencyService;
use App\Nexus\Modules\ShopAttribute\Models\AttributeOption;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Enums\PackageType;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Public catalog listing with filters/sort/search/pagination — no
     * equivalent query existed anywhere in the app before this (confirmed
     * during planning), only the admin CRUD listing.
     */
    public function index(Request $request, ?ShopCategory $category = null): View
    {
        if ($category && ! $category->publish) {
            abort(404);
        }

        $categoryIds = $category
            ? $category->descendants()->pluck('id')->push($category->id)
            : null;

        $query = ShopProduct::query()->publish()->with(['category', 'attributeOptions.attribute']);

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        $selectedAttrIds = array_values(array_filter(array_map('intval', (array) $request->query('attr', []))));
        if ($selectedAttrIds) {
            $query->whereHas('attributeOptions', fn ($q) => $q->whereIn('attribute_options.id', $selectedAttrIds));
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', CurrencyService::convertFromSessionCurrency($request->query('price_min')));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', CurrencyService::convertFromSessionCurrency($request->query('price_max')));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->query('q').'%');
        }

        $packageType = PackageType::tryFrom((string) $request->query('package_type'));
        if ($packageType) {
            $query->where('package_type', $packageType->value);
        }

        match ($request->query('sort')) {
            'new' => $query->latest(),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            // 'popular' has no real metric anywhere in the app yet (confirmed
            // during planning) — same latest() fallback used on the Home page.
            default => $query->latest(),
        };

        $products = $query->paginate(24)->appends($request->query());

        // Attribute options actually assigned to at least one published
        // product in scope, for the filter sidebar — no existing helper for
        // this, manual join through the pivot table (AttributeOption has no
        // inverse relation back to ShopProduct).
        $filterOptionIds = DB::table('product_attribute_options')
            ->join('shop_products', 'shop_products.id', '=', 'product_attribute_options.product_id')
            ->where('shop_products.publish', true)
            ->when($categoryIds, fn ($q) => $q->whereIn('shop_products.category_id', $categoryIds))
            ->pluck('product_attribute_options.attribute_option_id')
            ->unique();

        $filterOptions = AttributeOption::query()
            ->with('attribute')
            ->whereIn('id', $filterOptionIds)
            ->orderBy('order')
            ->get()
            ->filter(fn (AttributeOption $option) => $option->attribute !== null)
            ->groupBy(fn (AttributeOption $option) => $option->attribute->name);

        return view('shopProduct::public.catalog', [
            'products' => $products,
            'category' => $category,
            'categories' => ShopCategory::query()->publish()->orderBy('name')->get(),
            'filterOptions' => $filterOptions,
            'selectedAttrIds' => $selectedAttrIds,
            'packageTypes' => PackageType::cases(),
            'selectedPackageType' => $packageType,
        ]);
    }
}
