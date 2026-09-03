<?php

namespace App\Services;

use App\Models\SavedFilter;
use Illuminate\Http\Request;

class SavedFilterService
{
    /**
     * Apply the user's default saved filter when an index page is opened
     * without explicit query parameters.
     */
    public function applyDefault(Request $request, string $resource): ?SavedFilter
    {
        if ($this->hasExplicitFilters($request)) {
            return null;
        }

        $user = $request->user();

        if (! $user) {
            return null;
        }

        $savedFilter = $user->savedFilters()
            ->where('resource_type', $resource)
            ->where('is_default', true)
            ->first();

        if (! $savedFilter || ! is_array($savedFilter->filters)) {
            return null;
        }

        $request->query->add($savedFilter->filters);
        $request->attributes->set('flowmanager.default_saved_filter_id', $savedFilter->id);

        return $savedFilter;
    }

    public function perPage(Request $request, int $default = 15): int
    {
        $requested = $request->integer('per_page', $default);

        return in_array($requested, [15, 25, 50, 100], true)
            ? $requested
            : $default;
    }

    private function hasExplicitFilters(Request $request): bool
    {
        return collect($request->query())
            ->except(['page'])
            ->filter(fn (mixed $value) => $value !== null && $value !== '' && $value !== [])
            ->isNotEmpty();
    }
}
