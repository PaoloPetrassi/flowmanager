<?php

use App\Http\Controllers\AssetController;
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
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureEmailVerifiedConfigured;
use App\Http\Middleware\EnsureTwoFactorConfigured;
use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

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
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

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
