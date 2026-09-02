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
        $this->authorizeIntegrations();

        return view('integrations.api', [
            'tokens' => auth()->user()->apiTokens()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeIntegrations();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['required', 'in:read,write'],
        ]);

        $plainToken = 'fm_'.Str::random(48);

        auth()->user()->apiTokens()->create([
            'name' => $data['name'],
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => substr($plainToken, 0, 12),
            'abilities' => $data['abilities'],
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return back()->with(
            'status',
            __('API token created. Copy it now: :token', ['token' => $plainToken])
        );
    }

    public function destroy(ApiToken $apiToken): RedirectResponse
    {
        $this->authorizeIntegrations();
        abort_unless($apiToken->user_id === auth()->id(), 403);

        $apiToken->delete();

        return back()->with('status', __('API token revoked.'));
    }

    private function authorizeIntegrations(): void
    {
        abort_unless(auth()->user()->hasPermission('integrations.manage'), 403);
    }
}
