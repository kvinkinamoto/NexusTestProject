<?php

namespace App\Nexus\Modules\Order\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Order\Requests\RedeemGiftRequest;
use App\Nexus\Modules\Order\Services\GiftCodeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GiftRedemptionController extends Controller
{
    public function __construct(private readonly GiftCodeService $giftCodeService) {}

    public function show(): View
    {
        return view('order::public.redeem-gift');
    }

    public function store(RedeemGiftRequest $request): RedirectResponse
    {
        try {
            $this->giftCodeService->redeem($request->string('code')->toString(), $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['code' => $e->getMessage()])->withInput();
        }

        return redirect()->route('account.orders')->with('status', __('order::translate.gift_code_redeemed_success'));
    }
}
