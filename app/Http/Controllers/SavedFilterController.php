<?php

namespace App\Http\Controllers;

use App\Models\SavedFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavedFilterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resource_type' => ['required', 'in:companies,contacts,projects,tasks,assets,tickets'],
            'name' => ['required', 'string', 'max:100'],
            'filters' => ['required', 'array'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $makeDefault = $request->boolean('is_default');

        DB::transaction(function () use ($request, $data, $makeDefault): void {
            if ($makeDefault) {
                $request->user()->savedFilters()
                    ->where('resource_type', $data['resource_type'])
                    ->update(['is_default' => false]);
            }

            $request->user()->savedFilters()->create([
                'resource_type' => $data['resource_type'],
                'name' => $data['name'],
                'filters' => $data['filters'],
                'is_default' => $makeDefault,
            ]);
        });

        return back()->with('status', __('Filter saved.'));
    }

    public function toggleDefault(Request $request, SavedFilter $savedFilter): RedirectResponse
    {
        $this->authorizeOwner($request, $savedFilter);

        DB::transaction(function () use ($request, $savedFilter): void {
            $wasDefault = $savedFilter->is_default;

            $request->user()->savedFilters()
                ->where('resource_type', $savedFilter->resource_type)
                ->update(['is_default' => false]);

            if (! $wasDefault) {
                $savedFilter->update(['is_default' => true]);
            }
        });

        return back()->with(
            'status',
            $savedFilter->fresh()->is_default
                ? __('Default filter updated.')
                : __('Default filter cleared.')
        );
    }

    public function destroy(Request $request, SavedFilter $savedFilter): RedirectResponse
    {
        $this->authorizeOwner($request, $savedFilter);
        $savedFilter->delete();

        return back()->with('status', __('Saved filter deleted.'));
    }

    private function authorizeOwner(Request $request, SavedFilter $savedFilter): void
    {
        abort_unless($savedFilter->user_id === $request->user()->id, 403);
    }
}
