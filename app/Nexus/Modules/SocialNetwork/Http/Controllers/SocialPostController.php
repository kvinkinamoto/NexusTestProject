<?php

namespace App\Nexus\Modules\SocialNetwork\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\SocialNetwork\Http\Resources\SocialPostResource;
use App\Nexus\Modules\SocialNetwork\Models\SocialPost;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialPost::query()->visible()->latestFirst();

        if ($platform = $request->input('platform')) {
            $query->platform($platform);
        }

        return SocialPostResource::collection($query->paginate((int) ($request->input('perPage') ?: 20)));
    }
}
