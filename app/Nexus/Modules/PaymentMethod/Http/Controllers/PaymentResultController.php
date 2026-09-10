<?php

namespace App\Nexus\Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\Controller;

class PaymentResultController extends Controller
{
    public function handle(int $orderId)
    {
        return redirect('/')->with('success', __('paymentMethod::translate.payment_success'));
    }
}
