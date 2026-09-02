# FlowManager development commands

This file records the commands used to scaffold and run the application modules. The project is developed directly on `main` unless a different branch is explicitly required.

## Local development

Open two Git Bash terminals in the project root.

Terminal 1:

```bash
npm run dev
```

Terminal 2:

```bash
php artisan serve
```

Application URL:

```text
http://127.0.0.1:8000
```

## Runtime directories

If a ZIP extraction removes empty Laravel runtime directories, recreate them from Git Bash with:

```bash
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p bootstrap/cache
```

Then clear cached application files:

```bash
php artisan optimize:clear
```

## Projects module scaffolding

```bash
php artisan make:enum ProjectStatus --string
php artisan make:enum ProjectPriority --string
php artisan make:model Project -m -f -s
php artisan make:controller ProjectController --resource --model=Project
php artisan make:request StoreProjectRequest
php artisan make:request UpdateProjectRequest
php artisan make:policy ProjectPolicy --model=Project
php artisan make:test ProjectManagementTest --pest

php artisan make:view projects.index
php artisan make:view projects.create
php artisan make:view projects.edit
php artisan make:view projects.show
php artisan make:view projects._form
```

Implemented relationships:

- Project belongs to Company.
- Project may reference a Contact.
- Project may have a manager User.
- Project has many Tasks.
- Project records the User that created it.

## Tasks module scaffolding

```bash
php artisan make:enum TaskStatus --string
php artisan make:enum TaskPriority --string
php artisan make:model Task -m -f -s
php artisan make:controller TaskController --resource --model=Task
php artisan make:request StoreTaskRequest
php artisan make:request UpdateTaskRequest
php artisan make:policy TaskPolicy --model=Task
php artisan make:test TaskManagementTest --pest

php artisan make:view tasks.index
php artisan make:view tasks.create
php artisan make:view tasks.edit
php artisan make:view tasks.show
php artisan make:view tasks._form
```

Implemented relationships:

- Task belongs to Project.
- Task may be assigned to a User.
- Task records the User that created it.

## Assets module scaffolding

```bash
php artisan make:enum AssetStatus --string
php artisan make:model Asset -m -f -s
php artisan make:controller AssetController --resource --model=Asset
php artisan make:request StoreAssetRequest
php artisan make:request UpdateAssetRequest
php artisan make:request AssignAssetRequest
php artisan make:policy AssetPolicy --model=Asset
php artisan make:test AssetManagementTest --pest

php artisan make:view assets.index
php artisan make:view assets.create
php artisan make:view assets.edit
php artisan make:view assets.show
php artisan make:view assets._form
php artisan make:view assets.assignment
```

Implemented relationships:

- Asset may belong to a Company.
- Asset may be assigned to a User.
- Asset records the User that created it.
- Operators can use the dedicated assignment workflow without receiving full asset CRUD permissions.

## Tickets module scaffolding

```bash
php artisan make:enum TicketStatus --string
php artisan make:enum TicketPriority --string
php artisan make:enum TicketCategory --string
php artisan make:model Ticket -m -f -s
php artisan make:controller TicketController --resource --model=Ticket
php artisan make:request StoreTicketRequest
php artisan make:request UpdateTicketRequest
php artisan make:policy TicketPolicy --model=Ticket
php artisan make:test TicketManagementTest --pest

php artisan make:view tickets.index
php artisan make:view tickets.create
php artisan make:view tickets.edit
php artisan make:view tickets.show
php artisan make:view tickets._form
```

Implemented relationships:

- Ticket may belong to a Company.
- Ticket may reference a Contact.
- Ticket may be assigned to a User.
- Ticket records the User that created it.

## Users administration scaffolding

`User` already exists in a standard Laravel installation, so do not recreate the model.

```bash
php artisan make:controller UserController --resource --model=User
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
php artisan make:policy UserPolicy --model=User
php artisan make:test UserManagementTest --pest

php artisan make:view users.index
php artisan make:view users.create
php artisan make:view users.edit
php artisan make:view users.show
php artisan make:view users._form
```

The Users module manages account details and role assignments. It also prevents deletion of the currently authenticated account and protects the last Administrator from being demoted.

## Roles administration scaffolding

The `Role` and `Permission` models already belong to FlowManager's authorization layer, so they are not recreated here.

```bash
php artisan make:controller RoleController --resource --model=Role
php artisan make:request StoreRoleRequest
php artisan make:request UpdateRoleRequest
php artisan make:policy RolePolicy --model=Role
php artisan make:test RoleManagementTest --pest

php artisan make:view roles.index
php artisan make:view roles.create
php artisan make:view roles.edit
php artisan make:view roles.show
php artisan make:view roles._form
```

System roles cannot be deleted. The Administrator role identity and permissions are protected from normal UI edits; application permissions are centrally synchronized by `RolePermissionSeeder`.

## Routes

The resource routes are maintained manually in `routes/web.php`:

```php
Route::resource('companies', CompanyController::class);
Route::resource('contacts', ContactController::class);
Route::resource('projects', ProjectController::class);
Route::resource('tasks', TaskController::class);
Route::resource('assets', AssetController::class);
Route::resource('tickets', TicketController::class);
Route::resource('users', UserController::class);
Route::resource('roles', RoleController::class);
```

Assets also use two dedicated routes for assignment/unassignment:

```php
Route::get('/assets/{asset}/assignment', [AssetController::class, 'editAssignment'])
    ->name('assets.assignment.edit');

Route::put('/assets/{asset}/assignment', [AssetController::class, 'updateAssignment'])
    ->name('assets.assignment.update');
```

## Database setup after pulling these modules

Apply the new tables first:

```bash
php artisan migrate
```

Then synchronize permissions and create demo data:

```bash
php artisan db:seed
```

`DatabaseSeeder` executes the authorization/user seeders first, then the business/demo dataset:

1. `RolePermissionSeeder`
2. `AdminUserSeeder`
3. `DemoUserSeeder` when demo mode is enabled
4. `CompanySeeder`
5. `ContactSeeder`
6. `ProjectSeeder`
7. `TaskSeeder`
8. `AssetSeeder`
9. `TicketSeeder`
10. `V09DemoSeeder`
11. `V013DemoSeeder`

The demo module seeders return without creating duplicates when their target table already contains records.

To run only the newly added data seeders:

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ProjectSeeder
php artisan db:seed --class=TaskSeeder
php artisan db:seed --class=AssetSeeder
php artisan db:seed --class=TicketSeeder
```

Current demo quantities are:

- 25 Projects
- 75 Tasks
- 40 Assets
- 50 Tickets

## Cache and validation commands

After migrations and seeding:

```bash
php artisan optimize:clear
php artisan route:list
php artisan test
```

Code formatting, when required:

```bash
./vendor/bin/pint
```

Frontend production build:

```bash
npm run build
```

## Permission matrix

### Administrator

Full access to all application permissions and administration modules.

### Manager

Full operational CRUD, report access/export, and read access to Users/Roles. User and role management are excluded.

### Operator

- Companies: view/create/update
- Contacts: view/create/update
- Projects: view
- Tasks: view/create/update
- Assets: view/assign
- Tickets: view/create/update
- Reports: view

### Viewer

Read-only access to Companies, Contacts, Projects, Tasks, Assets, Tickets and Reports. Administration permissions are intentionally excluded.

## Git workflow

Development is performed directly on `main`.

Before starting:

```bash
git switch main
git pull --ff-only origin main
git status
```

After a completed and tested change:

```bash
git add .
git commit -m "Add remaining FlowManager modules"
git push origin main
```

Useful verification:

```bash
git status
git log --oneline -10
```

---

## v0.5 Collaboration & Audit scaffolding

The v0.5 implementation adds shared infrastructure instead of duplicating collaboration tables for every module.

### Audit log

```bash
php artisan make:model AuditLog -m
php artisan make:controller AuditLogController
php artisan make:test AuditLogTest --pest
```

Supporting application classes:

```bash
php artisan make:class Services/AuditService
php artisan make:trait Models/Concerns/Auditable
php artisan make:class Support/FlowResourceRegistry
```

`Auditable` is used by the operational models plus User and Role. The generic `audit_logs` table stores the model class and record ID, so no module-specific audit tables are required.

### Comments

```bash
php artisan make:model Comment -m
php artisan make:controller CommentController
php artisan make:request StoreCommentRequest
```

### Attachments

```bash
php artisan make:model Attachment -m
php artisan make:controller AttachmentController
php artisan make:request StoreAttachmentRequest
```

Comments and attachments are polymorphic and are exposed to operational records through the shared `HasCollaboration` trait:

```bash
php artisan make:trait Models/Concerns/HasCollaboration
```

Attachments are deliberately stored on the private `local` disk and are served by `AttachmentController::download()`. Do not replace this with direct public URLs unless the authorization model is redesigned accordingly.

### Notifications

```bash
php artisan make:notification FlowNotification
php artisan make:observer WorkAssignmentObserver
php artisan make:controller NotificationController
php artisan make:test NotificationCenterTest --pest
```

For a new Laravel project the standard notification table can normally be scaffolded with:

```bash
php artisan make:notifications-table
```

FlowManager v0.5 already contains its notification migration, so do not run that generation command on an updated checkout.

### Global search

```bash
php artisan make:controller SearchController
php artisan make:test GlobalSearchTest --pest
```

The controller searches only model classes that the current user is authorized to view. Search is intentionally server-side and permission-aware.

### Trash and restore

```bash
php artisan make:controller TrashController
php artisan make:test TrashManagementTest --pest
```

The trash operates on existing models that already use `SoftDeletes`; it does not need another record table.

### Shared collaboration tests

```bash
php artisan make:test CollaborationTest --pest
```

### Apply v0.5 to an existing database

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

The v0.5 migration set creates:

1. `audit_logs`
2. `comments`
3. `attachments`
4. `notifications`

The permission seeder adds:

- `audit.view`
- `comments.create`
- `comments.delete`
- `attachments.create`
- `attachments.delete`
- `trash.view`
- `trash.restore`
- `trash.delete`

### v0.5 routes

The shared routes are registered manually inside the authenticated route group in `routes/web.php`:

```php
Route::get('/search', [SearchController::class, 'index'])
    ->name('search.index');

Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');

Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
    ->name('notifications.open');

Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])
    ->name('notifications.read');

Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])
    ->name('notifications.read-all');

Route::post('/collaboration/{type}/{id}/comments', [CommentController::class, 'store'])
    ->name('comments.store');

Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy');

Route::post('/collaboration/{type}/{id}/attachments', [AttachmentController::class, 'store'])
    ->name('attachments.store');

Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
    ->name('attachments.download');

Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])
    ->name('attachments.destroy');

Route::get('/activity', [AuditLogController::class, 'index'])
    ->name('activity.index');

Route::get('/trash', [TrashController::class, 'index'])
    ->name('trash.index');

Route::patch('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])
    ->name('trash.restore');

Route::delete('/trash/{type}/{id}', [TrashController::class, 'destroy'])
    ->name('trash.destroy');
```

### v0.5 role additions

- **Administrator** — all collaboration, audit and trash permissions including permanent deletion.
- **Manager** — audit visibility, collaboration management, trash visibility and restore; no permanent trash deletion.
- **Operator** — comment creation and attachment upload in addition to existing operational permissions. Authors can remove their own collaboration items.
- **Viewer** — remains read-only and cannot add comments/files or use administration utilities.

---

## v0.6 Analytics & Planning scaffolding

v0.6 intentionally reuses the existing database schema. No v0.6 migration or seeder is required beyond re-running `RolePermissionSeeder` when upgrading from a version that does not yet contain report permissions.

### Reports

```bash
php artisan make:controller ReportController
php artisan make:class Services/ReportService
php artisan make:class Services/PdfReportService
php artisan make:test ReportManagementTest --pest
```

Views created manually:

```text
resources/views/reports/index.blade.php
resources/views/reports/excel.blade.php
resources/views/reports/print.blade.php
```

The Excel export uses SpreadsheetML and therefore does not require PhpSpreadsheet. `PdfReportService` generates a lightweight native PDF table without Dompdf, while the print view remains available for browser printing.

### Calendar

```bash
php artisan make:controller CalendarController
php artisan make:test PlanningToolsTest --pest
```

View:

```text
resources/views/calendar/index.blade.php
```

The calendar reads existing `projects.due_date`, `tasks.due_date` and `assets.warranty_expires_at` values.

### Kanban

```bash
php artisan make:controller BoardController
```

View:

```text
resources/views/boards/index.blade.php
```

Status changes use the existing Task and Ticket policies. JavaScript drag-and-drop is progressive enhancement: the status `<select>` + submit button remains the non-drag fallback.

### Dashboard analytics

```bash
php artisan make:test DashboardAnalyticsTest --pest
```

No chart package is installed. The dashboard's visualizations are generated from database aggregates and rendered with local HTML/CSS.

### v0.6 routes

```php
Route::get('/calendar', [CalendarController::class, 'index'])
    ->name('calendar.index');

Route::get('/boards', [BoardController::class, 'index'])
    ->name('boards.index');

Route::patch('/boards/tasks/{task}/status', [BoardController::class, 'updateTaskStatus'])
    ->name('boards.tasks.status');

Route::patch('/boards/tickets/{ticket}/status', [BoardController::class, 'updateTicketStatus'])
    ->name('boards.tickets.status');

Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index');

Route::get('/reports/export/csv', [ReportController::class, 'csv'])
    ->name('reports.csv');

Route::get('/reports/export/excel', [ReportController::class, 'excel'])
    ->name('reports.excel');

Route::get('/reports/export/pdf', [ReportController::class, 'pdf'])
    ->name('reports.pdf');

Route::get('/reports/print', [ReportController::class, 'print'])
    ->name('reports.print');
```

### Upgrade commands for v0.5 + v0.6

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

## v1.1–v1.3 release and integration commands

Rebuild the local demo dataset:

```bash
php artisan flowmanager:demo-reset --force --admin-password="YOUR_ADMIN_PASSWORD"
```

Create a clean source release ZIP after building the frontend:

```bash
npm run build
php artisan flowmanager:package-release
```

Open the machine-readable API specification locally:

```text
http://127.0.0.1:8000/api/openapi.json
```

The authenticated human-readable documentation is available from **Administration → API documentation**.
