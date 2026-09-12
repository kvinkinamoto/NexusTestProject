<?php

namespace App\Nexus\Modules\Order\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountOrdersController extends Controller
{
    public function index(): View
    {
        $orders = auth()->user()->orders()->with(['products', 'status'])->latest()->paginate(10);

        return view('order::public.account-orders', ['orders' => $orders]);
    }
}
