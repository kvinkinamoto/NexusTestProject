<?php

namespace App\Nexus\Modules\Advertising\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Advertising\Models\Advertising;
use Illuminate\Http\RedirectResponse;

class AdClickController extends Controller
{
    public function __invoke(Advertising $advertising): RedirectResponse
    {
        if (! $advertising->target_url) {
            abort(404);
        }

        $advertising->recordClick();

        return redirect()->away($advertising->target_url);
    }
}
