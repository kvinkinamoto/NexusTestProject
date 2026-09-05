<?php

namespace App\Nexus\Modules\Page\Http\Controllers;

use App\Nexus\Modules\Page\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('page::public.page', ['page' => $page]);
    }
}
