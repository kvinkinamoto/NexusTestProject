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
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly WishlistRepository $wishlistRepository,
    ) {}

    /**
     * A guest is identified by their session id, same as the Cart module.
     */
    private function sessionId(Request $request): ?string
    {
        return auth()->check() ? null : $request->session()->getId();
    }

    public function getList(Request $request)
    {
        return WishlistItemResource::collection($this->wishlistRepository->getList(auth()->user(), $this->sessionId($request)));
    }

    public function add(AddWishlistRequest $request)
    {
        $this->wishlistService->add(auth()->user(), $this->sessionId($request), $request->validated('product_id'));

        return response()->json(['success' => true]);
    }

    public function remove(RemoveWishlistRequest $request)
    {
        $this->wishlistService->remove(auth()->user(), $this->sessionId($request), $request->validated('product_id'));

        return response()->json(['success' => true]);
    }

    public function sync(SyncWishlistRequest $request)
    {
        $user = auth()->user();
        $sessionId = $this->sessionId($request);
        $this->wishlistService->sync($user, $sessionId, $request->validated('items'));

        return WishlistItemResource::collection($this->wishlistRepository->getList($user, $sessionId));
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
