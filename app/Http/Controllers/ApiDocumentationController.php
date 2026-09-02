<?php

namespace App\Http\Controllers;

use App\Support\OpenApiSpecification;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiDocumentationController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('integrations.manage'), 403);

        return view('integrations.documentation', [
            'resources' => ['companies', 'contacts', 'projects', 'tasks', 'tickets', 'assets'],
            'webhookEvents' => ['created', 'updated', 'deleted', 'task.completed', 'ticket.resolved'],
        ]);
    }

    public function specification(): JsonResponse
    {
        return response()->json(
            OpenApiSpecification::build(),
            200,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}
