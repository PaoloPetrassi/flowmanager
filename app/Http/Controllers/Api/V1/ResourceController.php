<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request, string $resource): JsonResponse
    {
        $this->ensureAbility($request, 'read');
        [$model, $permission] = $this->resourceConfig($resource);

        abort_unless($request->user()->hasPermission($permission), 403);

        $query = $model::query();

        if (method_exists($model, 'scopeOperational')) {
            $query->operational();
        }

        $rows = $query
            ->latest('id')
            ->paginate(min(max((int) $request->query('per_page', 25), 1), 100));

        return response()->json($rows);
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        $this->ensureAbility($request, 'read');
        [$model, $permission] = $this->resourceConfig($resource);

        abort_unless($request->user()->hasPermission($permission), 403);

        $row = $model::query()->findOrFail($id);

        return response()->json(['data' => $row]);
    }

    private function resourceConfig(string $resource): array
    {
        return [
            'companies' => [Company::class, 'companies.view'],
            'contacts' => [Contact::class, 'contacts.view'],
            'projects' => [Project::class, 'projects.view'],
            'tasks' => [Task::class, 'tasks.view'],
            'tickets' => [Ticket::class, 'tickets.view'],
            'assets' => [Asset::class, 'assets.view'],
        ][$resource] ?? abort(404);
    }

    private function ensureAbility(Request $request, string $ability): void
    {
        abort_unless(
            in_array($ability, $request->attributes->get('apiToken')?->abilities ?? [], true),
            403
        );
    }
}
