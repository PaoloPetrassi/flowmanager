<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandPaletteController extends Controller
{
    public function __invoke(Request $r): JsonResponse
    {
        $q = trim((string) $r->query('q'));
        $results = collect();
        if (mb_strlen($q) >= 2) {
            if ($r->user()->hasPermission('companies.view')) {
                $results = $results->merge(Company::where('name', 'like', "%$q%")->limit(4)->get()->map(fn ($m) => ['label' => $m->name, 'type' => __('Company'), 'url' => route('companies.show', $m)]));
            }if ($r->user()->hasPermission('projects.view')) {
                $results = $results->merge(Project::operational()->where(fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"))->limit(4)->get()->map(fn ($m) => ['label' => $m->code.' · '.$m->name, 'type' => __('Project'), 'url' => route('projects.show', $m)]));
            }if ($r->user()->hasPermission('tasks.view')) {
                $results = $results->merge(Task::where('title', 'like', "%$q%")->limit(4)->get()->map(fn ($m) => ['label' => $m->title, 'type' => __('Task'), 'url' => route('tasks.show', $m)]));
            }if ($r->user()->hasPermission('tickets.view')) {
                $results = $results->merge(Ticket::where(fn ($x) => $x->where('subject', 'like', "%$q%")->orWhere('reference', 'like', "%$q%"))->limit(4)->get()->map(fn ($m) => ['label' => $m->reference.' · '.$m->subject, 'type' => __('Ticket'), 'url' => route('tickets.show', $m)]));
            }
        }

return response()->json(['results' => $results->values()->take(12)]);
    }
}
