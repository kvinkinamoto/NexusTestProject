<?php

namespace App\Nexus\Modules\Wishlist\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Wishlist\Repositories\WishlistRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistPageController extends Controller
{
    /**
     * Guest-accessible, same identity resolution as CartPageController —
     * wishlist is session-scoped for guests, user-scoped once logged in.
     */
    public function index(Request $request, WishlistRepository $wishlistRepository): View
    {
        $user = auth()->user();
        $sessionId = $user ? null : $request->session()->getId();

        $products = $wishlistRepository->getDetailsForUser($user, $sessionId)->load('category');

        return view('wishlist::public.wishlist', ['products' => $products]);
    }
}
