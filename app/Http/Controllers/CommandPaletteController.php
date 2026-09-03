<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandPaletteController extends Controller
{
    public function __invoke(Request $request, GlobalSearchService $searchService): JsonResponse
    {
        $query = trim((string) $request->query('q'));

        return response()->json([
            'results' => mb_strlen($query) >= 2
                ? $searchService->palette($request->user(), $query)
                : [],
        ]);
    }
}
