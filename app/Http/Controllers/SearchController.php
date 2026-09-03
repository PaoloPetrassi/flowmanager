<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request, GlobalSearchService $searchService): View
    {
        $query = trim((string) $request->query('q'));
        $scopes = $searchService->scopes($request->user());
        $scope = (string) $request->query('scope');
        $scope = array_key_exists($scope, $scopes) ? $scope : '';
        $groups = [];

        if (mb_strlen($query) >= 2) {
            $groups = $searchService->groups(
                $request->user(),
                $query,
                $scope !== '' ? $scope : null
            );
        }

        return view('search.index', [
            'query' => $query,
            'scope' => $scope,
            'scopes' => $scopes,
            'groups' => $groups,
            'resultCount' => collect($groups)->sum(fn (array $group) => count($group['items'])),
        ]);
    }
}
