<?php

namespace App\Http\Controllers;

use App\Models\ScheduledReport;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledReportController extends Controller
{
    public function index(ReportService $reports): View
    {
        abort_unless(auth()->user()->hasPermission('analytics.view'), 403);

        return view('analytics.schedules', [
            'schedules' => ScheduledReport::where('user_id', auth()->id())->latest()->get(),
            'reportTypes' => $reports::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('analytics.view'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'report_type' => ['required', 'in:projects,tasks,tickets,assets'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        ScheduledReport::create($data + [
            'user_id' => auth()->id(),
            'is_active' => true,
            'next_run_at' => now(),
        ]);

        return back()->with('status', __('Scheduled report created.'));
    }

    public function destroy(ScheduledReport $scheduledReport): RedirectResponse
    {
        abort_unless($scheduledReport->user_id === auth()->id() || auth()->user()->hasRole('administrator'), 403);
        $scheduledReport->delete();
        return back()->with('status', __('Scheduled report deleted.'));
    }
}
