<?php

namespace App\Nexus\Modules\License\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\License\Models\License;
use App\Nexus\Modules\LicenseActivation\Models\LicenseActivation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccountLicensesController extends Controller
{
    public function index(): View
    {
        $licenses = auth()->user()->licenses()
            ->with(['product.releases' => fn ($query) => $query->published(), 'activations'])
            ->latest()
            ->get()
            ->each(fn (License $license) => $license->download_url = self::signedDownloadUrl($license));

        return view('license::public.account-licenses', ['licenses' => $licenses]);
    }

    public function activate(Request $request, License $license): RedirectResponse
    {
        $this->authorizeOwner($license);

        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        if (! $license->isActive()) {
            throw ValidationException::withMessages(['identifier' => 'Цю ліцензію відкликано.']);
        }

        if ($license->activations()->count() >= $license->max_activations) {
            throw ValidationException::withMessages(['identifier' => 'Досягнуто ліміту активацій цієї ліцензії.']);
        }

        if ($license->activations()->where('identifier', $data['identifier'])->exists()) {
            throw ValidationException::withMessages(['identifier' => 'Цей проєкт вже активовано за цією ліцензією.']);
        }

        $license->activations()->create([
            'identifier' => $data['identifier'],
            'activated_at' => now(),
        ]);

        return back()->with('status', 'Ліцензію активовано.');
    }

    public function deactivate(License $license, LicenseActivation $activation): RedirectResponse
    {
        $this->authorizeOwner($license);

        if ($activation->license_id !== $license->id) {
            abort(404);
        }

        $activation->delete();

        return back()->with('status', 'Активацію знято.');
    }

    public function download(License $license): BinaryFileResponse
    {
        $this->authorizeOwner($license);

        if (! $license->isActive()) {
            abort(403, 'Цю ліцензію відкликано.');
        }

        $release = $license->product->releases()->published()->first();

        if (! $release || ! $release->zip_disk_path || ! file_exists($release->zip_disk_path)) {
            abort(404, 'Для цього продукту ще немає доступного релізу.');
        }

        return response()->download(
            $release->zip_disk_path,
            "{$license->product->slug}-{$release->version}.zip",
        );
    }

    public static function signedDownloadUrl(License $license): string
    {
        return URL::temporarySignedRoute('account.licenses.download', now()->addMinutes(5), ['license' => $license->id]);
    }

    private function authorizeOwner(License $license): void
    {
        if ($license->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
