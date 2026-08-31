<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SavedReport;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $r): View
    {
        abort_unless(auth()->user()->hasPermission('analytics.view'), 403);
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth())->push(now()->startOfMonth());
        $trend = $months->map(fn ($m) => ['label' => $m->translatedFormat('M Y'), 'tasks' => Task::whereBetween('completed_at', [$m, $m->copy()->endOfMonth()])->count(), 'tickets' => Ticket::whereBetween('resolved_at', [$m, $m->copy()->endOfMonth()])->count(), 'hours' => round(TimeEntry::whereBetween('started_at', [$m, $m->copy()->endOfMonth()])->sum('minutes') / 60, 1)]);
        $projectStatus = Project::where('is_template', false)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $taskStatus = Task::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $ticketPriority = Ticket::whereNotIn('status', ['closed', 'resolved'])->selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority');

        return view('analytics.index', ['trend' => $trend, 'projectStatus' => $projectStatus, 'taskStatus' => $taskStatus, 'ticketPriority' => $ticketPriority, 'savedReports' => SavedReport::query()->where(fn ($q) => $q->where('user_id', auth()->id())->orWhere('is_shared', true))->latest()->get()]);
    }

    public function storeReport(Request $r)
    {
        abort_unless(auth()->user()->hasPermission('analytics.view'), 403);
        $d = $r->validate(['name' => 'required|string|max:100', 'dataset' => 'required|in:projects,tasks,tickets,time', 'chart_type' => 'required|in:bar,line,pie']);
        auth()->user()->savedReports()->create($d + ['filters' => $r->except(['_token', 'name', 'dataset', 'chart_type'])]);

        return back()->with('status', __('Report saved.'));
    }

    public function destroyReport(SavedReport $savedReport)
    {
        abort_unless($savedReport->user_id === auth()->id() || auth()->user()->hasRole('administrator'), 403);
        $savedReport->delete();

        return back()->with('status', __('Saved report deleted.'));
    }
}
