<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\BulkActionController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\SavedFilterController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CommandPaletteController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPlanningController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\ProjectTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ScheduledReportController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\SystemJobsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureEmailVerifiedConfigured;
use App\Http\Middleware\EnsureTwoFactorConfigured;
use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');

    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('two-factor.verify');
});

Route::middleware([
    'auth',
    EnsureTwoFactorConfigured::class,
    EnsureEmailVerifiedConfigured::class,
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/schedules', [ScheduledReportController::class, 'index'])->name('analytics.schedules.index');
    Route::post('/analytics/schedules', [ScheduledReportController::class, 'store'])->name('analytics.schedules.store');
    Route::delete('/analytics/schedules/{scheduledReport}', [ScheduledReportController::class, 'destroy'])->name('analytics.schedules.destroy');

    Route::post('/analytics/reports', [AnalyticsController::class, 'storeReport'])->name('analytics.reports.store');
    Route::delete('/analytics/reports/{savedReport}', [AnalyticsController::class, 'destroyReport'])->name('analytics.reports.destroy');

    Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
    Route::post('/imports/preview', [ImportController::class, 'preview'])->name('imports.preview');
    Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');

    Route::post('/bulk/{resource}', [BulkActionController::class, 'update'])->name('bulk.update');
    Route::post('/saved-filters', [SavedFilterController::class, 'store'])->name('saved-filters.store');
    Route::delete('/saved-filters/{savedFilter}', [SavedFilterController::class, 'destroy'])->name('saved-filters.destroy');

    Route::get('/preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
    Route::put('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');

    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields.index');
    Route::post('/custom-fields', [CustomFieldController::class, 'store'])->name('custom-fields.store');
    Route::put('/custom-fields/{customField}', [CustomFieldController::class, 'update'])->name('custom-fields.update');
    Route::delete('/custom-fields/{customField}', [CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');

    Route::get('/integrations/api', [ApiTokenController::class, 'index'])->name('integrations.api.index');
    Route::post('/integrations/api', [ApiTokenController::class, 'store'])->name('integrations.api.store');
    Route::delete('/integrations/api/{apiToken}', [ApiTokenController::class, 'destroy'])->name('integrations.api.destroy');
    Route::get('/integrations/webhooks', [WebhookController::class, 'index'])->name('integrations.webhooks.index');
    Route::post('/integrations/webhooks', [WebhookController::class, 'store'])->name('integrations.webhooks.store');
    Route::delete('/integrations/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('integrations.webhooks.destroy');

    Route::get('/document-templates', [DocumentTemplateController::class, 'index'])->name('document-templates.index');
    Route::post('/document-templates', [DocumentTemplateController::class, 'store'])->name('document-templates.store');
    Route::delete('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->name('document-templates.destroy');
    Route::get('/document-templates/{documentTemplate}/generate/{type}/{id}', [DocumentTemplateController::class, 'generate'])->name('document-templates.generate');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::patch('/documents/{attachment}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    Route::patch('/documents/{attachment}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
    Route::post('/documents/{attachment}/version', [DocumentController::class, 'newVersion'])->name('documents.version');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/command-palette', CommandPaletteController::class)->name('command-palette');

    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::put('/security/password', [SecurityController::class, 'updatePassword'])->name('security.password');
    Route::post('/security/two-factor', [SecurityController::class, 'beginTwoFactor'])->name('security.two-factor.begin');
    Route::post('/security/two-factor/confirm', [SecurityController::class, 'confirmTwoFactor'])->name('security.two-factor.confirm');
    Route::delete('/security/two-factor', [SecurityController::class, 'disableTwoFactor'])->name('security.two-factor.disable');
    Route::delete('/security/sessions/{session}', [SecurityController::class, 'destroySession'])->name('security.sessions.destroy');
    Route::delete('/security/sessions', [SecurityController::class, 'destroyOtherSessions'])->name('security.sessions.destroy-others');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/export.ics', [CalendarController::class, 'ics'])->name('calendar.ics');
    Route::get('/boards', [BoardController::class, 'index'])->name('boards.index');
    Route::patch('/boards/tasks/{task}/status', [BoardController::class, 'updateTaskStatus'])->name('boards.tasks.status');
    Route::patch('/boards/tickets/{ticket}/status', [BoardController::class, 'updateTicketStatus'])->name('boards.tickets.status');

    Route::get('/planning/gantt', [ProjectPlanningController::class, 'gantt'])->name('planning.gantt');
    Route::get('/planning/workload', [ProjectPlanningController::class, 'workload'])->name('planning.workload');

    Route::get('/project-templates', [ProjectTemplateController::class, 'index'])->name('project-templates.index');
    Route::post('/projects/{project}/template', [ProjectTemplateController::class, 'storeFromProject'])->name('projects.template');
    Route::post('/projects/{project}/duplicate', [ProjectTemplateController::class, 'duplicate'])->name('projects.duplicate');
    Route::post('/project-templates/{template}/instantiate', [ProjectTemplateController::class, 'instantiate'])->name('project-templates.instantiate');
    Route::delete('/project-templates/{template}', [ProjectTemplateController::class, 'destroy'])->name('project-templates.destroy');

    Route::post('/projects/{project}/team', [ProjectTeamController::class, 'store'])->name('projects.team.store');
    Route::delete('/projects/{project}/team/{user}', [ProjectTeamController::class, 'destroy'])->name('projects.team.destroy');
    Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store'])->name('projects.milestones.store');
    Route::patch('/projects/{project}/milestones/{milestone}', [MilestoneController::class, 'toggle'])->name('projects.milestones.toggle');
    Route::delete('/projects/{project}/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('projects.milestones.destroy');

    Route::post('/tasks/{task}/time/start', [TimeEntryController::class, 'start'])->name('tasks.time.start');
    Route::patch('/tasks/{task}/time/stop', [TimeEntryController::class, 'stop'])->name('tasks.time.stop');
    Route::post('/tasks/{task}/time', [TimeEntryController::class, 'store'])->name('tasks.time.store');
    Route::delete('/tasks/{task}/time/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('tasks.time.destroy');

    Route::get('/automations', [AutomationController::class, 'index'])->name('automations.index');
    Route::post('/automations', [AutomationController::class, 'store'])->name('automations.store');
    Route::put('/automations/{automation}', [AutomationController::class, 'update'])->name('automations.update');
    Route::delete('/automations/{automation}', [AutomationController::class, 'destroy'])->name('automations.destroy');
    Route::post('/automations/run', [AutomationController::class, 'run'])->name('automations.run');

    Route::get('/system', [SystemController::class, 'index'])->name('system.index');
    Route::get('/system/jobs', [SystemJobsController::class, 'index'])->name('system.jobs.index');
    Route::post('/system/jobs/failed/{uuid}/retry', [SystemJobsController::class, 'retry'])->name('system.jobs.retry');
    Route::delete('/system/jobs/failed/{uuid}', [SystemJobsController::class, 'forget'])->name('system.jobs.forget');
    Route::delete('/system/jobs/completed', [SystemJobsController::class, 'clearCompleted'])->name('system.jobs.clear-completed');
    Route::post('/system/backups', [SystemController::class, 'createBackup'])->name('system.backups.store');
    Route::get('/system/backups/{filename}', [SystemController::class, 'downloadBackup'])->name('system.backups.download');
    Route::delete('/system/backups/{filename}', [SystemController::class, 'deleteBackup'])->name('system.backups.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'csv'])->name('reports.csv');
    Route::get('/reports/export/excel', [ReportController::class, 'excel'])->name('reports.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::post('/collaboration/{type}/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/collaboration/{type}/{id}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/activity', [AuditLogController::class, 'index'])->name('activity.index');
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::patch('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/{type}/{id}', [TrashController::class, 'destroy'])->name('trash.destroy');

    Route::resource('companies', CompanyController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('projects', ProjectController::class);

    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
    Route::resource('tasks', TaskController::class);

    Route::get('/assets/{asset}/assignment', [AssetController::class, 'editAssignment'])->name('assets.assignment.edit');
    Route::put('/assets/{asset}/assignment', [AssetController::class, 'updateAssignment'])->name('assets.assignment.update');
    Route::resource('assets', AssetController::class);

    Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
    Route::patch('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::resource('tickets', TicketController::class);

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
