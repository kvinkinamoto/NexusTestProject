<?php

namespace App\Nexus\Modules\Wishlist\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Wishlist\Http\Resources\WishlistItemResource;
use App\Nexus\Modules\Wishlist\Http\Resources\WishlistProductResource;
use App\Nexus\Modules\Wishlist\Repositories\WishlistRepository;
use App\Nexus\Modules\Wishlist\Requests\AddWishlistRequest;
use App\Nexus\Modules\Wishlist\Requests\RemoveWishlistRequest;
use App\Nexus\Modules\Wishlist\Requests\ResolveWishlistRequest;
use App\Nexus\Modules\Wishlist\Requests\SyncWishlistRequest;
use App\Nexus\Modules\Wishlist\Services\WishlistService;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly WishlistRepository $wishlistRepository,
    ) {}

    public function getList()
    {
        return WishlistItemResource::collection($this->wishlistRepository->getList(auth()->user()));
    }

    public function add(AddWishlistRequest $request)
    {
        $this->wishlistService->add(auth()->user(), $request->validated('product_id'));

        return response()->json(['success' => true]);
    }

    public function remove(RemoveWishlistRequest $request)
    {
        $this->wishlistService->remove(auth()->user(), $request->validated('product_id'));

        return response()->json(['success' => true]);
    }

    public function sync(SyncWishlistRequest $request)
    {
        $user = auth()->user();
        $this->wishlistService->sync($user, $request->validated('items'));

        return WishlistItemResource::collection($this->wishlistRepository->getList($user));
    }

    public function getDetails()
    {
        return WishlistProductResource::collection($this->wishlistRepository->getDetailsForUser(auth()->user()));
    }

    public function resolve(ResolveWishlistRequest $request)
    {
        return WishlistProductResource::collection($this->wishlistRepository->resolveItems($request->validated('items')));
    }
}
