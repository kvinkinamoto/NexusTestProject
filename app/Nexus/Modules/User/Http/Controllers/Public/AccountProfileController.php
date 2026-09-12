<?php

namespace App\Nexus\Modules\User\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\User\Requests\UpdatePasswordRequest;
use App\Nexus\Modules\User\Requests\UpdateProfileRequest;
use App\Nexus\Modules\User\Services\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountProfileController extends Controller
{
    public function edit(): View
    {
        return view('user::public.account-profile');
    }

    public function update(UpdateProfileRequest $request, UserProfileService $service): RedirectResponse
    {
        $service->updateProfile($request->user(), $request->validated());

        return back()->with('status', 'Дані профілю оновлено.');
    }

    public function updatePassword(UpdatePasswordRequest $request, UserProfileService $service): RedirectResponse
    {
        $service->updatePassword($request->user(), $request->validated()['password']);

        return back()->with('status', 'Пароль оновлено.');
    }
}
