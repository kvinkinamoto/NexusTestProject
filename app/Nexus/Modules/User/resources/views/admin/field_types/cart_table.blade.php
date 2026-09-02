@php
    if (!isset($model) || !$model->exists) {
        return;
    }

    $cart = $model->cart()
        ->with(['products.product:id,name,price,price_discount', 'products.product.image'])
        ->first();

    $items = $cart
        ? $cart->products->map(function ($row) {
            $product = $row->product;
            $image = $product?->relationLoaded('image') ? $product->image : null;
            $effective = $product?->price_discount ?? $product?->price;

            return [
                'product' => $product,
                'product_id' => $row->product_id,
                'quantity' => (int) $row->quantity,
                'image' => ($image && $image->path) ? asset($image->path) : null,
                'price' => $product?->price,
                'price_discount' => $product?->price_discount,
                'effective_price' => $effective,
                'line_total' => $effective !== null
                    ? (float) $effective * (int) $row->quantity
                    : null,
            ];
        })
        : collect();

    $fmtPrice = static function ($value) {
        return $value === null ? '—' : number_format((float) $value, 2, '.', ' ');
    };
@endphp

<style>
    #user_cart_table .cart-product-photo img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--bs-border-color, #e9ebec);
    }
    #user_cart_table .cart-product-photo .no-image {
        width: 48px;
        height: 48px;
        background: var(--bs-light, #f5f6f8);
        border: 1px dashed var(--bs-border-color, #e9ebec);
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--bs-secondary, #adb5bd);
    }
    #user_cart_table .cart-product-price .price-current {
        font-weight: 600;
    }
    #user_cart_table .cart-product-price.has-discount .price-current {
        color: var(--bs-danger, #f06548);
    }
    #user_cart_table .cart-product-price .price-old {
        color: var(--bs-secondary-color, #98a6ad);
        font-size: .85em;
        margin-left: .4rem;
        text-decoration: line-through;
    }
</style>

<div id="user_cart_table" class="mb-3">
    @if($items->isEmpty())
        <div class="text-muted">@lang('user::translate.no_cart_products')</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">@lang('cart::translate.image')</th>
                        <th>@lang('cart::translate.product')</th>
                        <th style="width: 110px;">@lang('cart::translate.quantity')</th>
                        <th style="width: 200px;">@lang('cart::translate.price')</th>
                        <th style="width: 130px;">@lang('cart::translate.total')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        @php
                            $product = $item['product'];
                            $hasDiscount = $item['price_discount'] !== null && $item['price'] !== null;
                        @endphp
                        <tr>
                            <td class="cart-product-photo text-center">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="">
                                @else
                                    <span class="no-image"><i class="bx bx-image fs-22"></i></span>
                                @endif
                            </td>
                            <td>
                                @if($product)
                                    <a href="{{ route('nexus.module.action', ['shopProduct', 'edit', 'id' => $product->id]) }}"
                                       target="_blank"
                                       class="text-body fw-medium">
                                        {{ $product->name }}
                                        <span class="text-muted">(ID: {{ $product->id }})</span>
                                    </a>
                                @else
                                    <span class="text-muted">
                                        @lang('cart::translate.deleted_product') (ID: {{ $item['product_id'] }})
                                    </span>
                                @endif
                            </td>
                            <td>{{ $item['quantity'] }}</td>
                            <td class="cart-product-price {{ $hasDiscount ? 'has-discount' : '' }}">
                                @if($hasDiscount)
                                    <span class="price-current">{{ $fmtPrice($item['price_discount']) }}</span>
                                    <span class="price-old">{{ $fmtPrice($item['price']) }}</span>
                                @else
                                    <span class="price-current">{{ $fmtPrice($item['price']) }}</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $fmtPrice($item['line_total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2">
                            <a href="{{ route('nexus.module.action', ['cart', 'edit', 'id' => $cart->id]) }}"
                               target="_blank"
                               class="btn btn-soft-primary btn-sm text-nowrap">
                                <i class="bx bx-link-external align-middle me-1"></i>
                                @lang('user::translate.open_cart')
                            </a>
                        </th>
                        <th colspan="2" class="text-end">@lang('cart::translate.cart_total')</th>
                        <th class="fw-semibold">{{ $fmtPrice($cart->getTotal()) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
