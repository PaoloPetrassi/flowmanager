<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('integrations.manage'), 403);

        return view('integrations.api', ['tokens' => auth()->user()->apiTokens()->latest()->get()]);
    }

    public function store(Request $r): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('integrations.manage'), 403);
        $d = $r->validate(['name' => 'required|string|max:100', 'expires_at' => 'nullable|date|after:today', 'abilities' => 'required|array|min:1', 'abilities.*' => 'in:read,write']);
        $plain = 'fm_'.Str::random(48);
        auth()->user()->apiTokens()->create(['name' => $d['name'], 'token_hash' => hash('sha256', $plain), 'token_prefix' => substr($plain, 0, 12), 'abilities' => $d['abilities'], 'expires_at' => $d['expires_at'] ?? null]);

        return back()->with('status', __('API token created. Copy it now: :token', ['token' => $plain]));
    }

    public function destroy(ApiToken $apiToken): RedirectResponse
    {
        abort_unless($apiToken->user_id === auth()->id(), 403);
        $apiToken->delete();

        return back()->with('status', __('API token revoked.'));
    }
}
