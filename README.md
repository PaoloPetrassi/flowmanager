# FlowManager

FlowManager is a Laravel management application combining CRM, projects, tasks, assets, support, collaboration, auditability, automation, planning, document management, integrations and business intelligence in a responsive bilingual interface.

Current application version: **v0.13 — Integration, Advanced UX, Documents & Business Intelligence**.

## Core modules

- **Dashboard** — operational KPIs, personal queues, throughput, workload and status analytics.
- **Companies / Contacts** — CRM registry and cross-module relationships.
- **Projects / Tasks** — teams, milestones, dependencies, recurring work, estimates, time tracking, templates, Gantt and workload.
- **Assets / Tickets** — inventory lifecycle, assignments, support workflow and SLA tracking.
- **Collaboration** — comments, private attachments, notifications and audit history.
- **Planning** — calendar, iCalendar export, Kanban, Gantt and workload.
- **Automation** — scheduled reminders, recurring work, SLA escalation and configurable rules.
- **Documents** — document registry, categories, expiry, versions, approval/rejection and templates.
- **Analytics** — KPIs, saved reports and scheduled CSV reports.
- **Integration** — CSV/XLSX imports, scoped API tokens, inbound ticket API and signed webhooks.
- **Administration** — users, roles, extensibility, trash/restore, security, backups and system health.

## v0.10 — CRM & Integration

- Guided **CSV/XLSX import** with preview, column mapping, validation and error summary.
- Bulk operations for Projects, Tasks and Tickets.
- Saved filters for the main operational registries.
- Cross-module tags.
- Administrator-defined custom fields.
- Scoped bearer API tokens.
- Read API for supported resources and a write-scoped inbound Ticket endpoint.
- Signed outbound webhooks with delivery history.
- iCalendar export for project and task deadlines.

XLSX import uses PHP's `ZipArchive`; CSV import has no additional PHP extension requirement beyond the normal Laravel stack.

## v0.11 — Advanced UX

- **Ctrl/Cmd + K Command Palette** for navigation, actions and record search.
- Persisted user appearance and density preferences.
- Light, dark and system themes.
- Comfortable and compact information density.
- Per-user table-column visibility for Projects, Tasks and Tickets.
- Persisted dashboard-widget visibility.
- Responsive behavior retained across the new tools.

## v0.12 — Document Management

Private attachments now support:

- document categories and status;
- version chains;
- expiry dates;
- SHA-256 checksum;
- approval/rejection with approver and timestamp;
- protected download routes.

A central Documents registry provides filtering and expiry visibility. Document Templates can generate:

- native FlowManager PDF output;
- Word-compatible `.doc` output;
- placeholders such as `{{name}}`, `{{code}}` and nested model paths such as `{{company.name}}`.

Document approval is an audited application workflow; it is **not a cryptographic digital signature**.

## v0.13 — Business Intelligence

- Business Intelligence dashboard with six-month throughput metrics.
- Project/task/ticket distribution analytics.
- Tracked-hours reporting.
- Saved analytics reports.
- Scheduled CSV reports delivered through the configured Laravel mail transport.
- Daily, weekly and monthly scheduling with execution/error tracking.

## Existing production and workflow capabilities

FlowManager also includes the features introduced in v0.7–v0.9:

- password reset, optional email verification and TOTP 2FA;
- active sessions and login history;
- System Health, scheduler heartbeat and portable backup/restore;
- ticket SLA, recurring tasks, task dependencies and automation rules;
- project teams, milestones, subtasks and time tracking;
- project templates, duplication, Gantt and workload;
- audit log, comments, notifications, global search and trash/restore;
- CSV/Excel/PDF reports, calendar and Kanban.

## Localization and responsive design

FlowManager supports **English and Italian**. Locale selection is persisted in session and cookie.

The interface is desktop-first but remains usable on tablets and phones. Dense surfaces use local horizontal scrolling rather than hiding operational information.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL/MariaDB or another Laravel-supported database
- PHP `zip` extension when importing XLSX files
- a configured Laravel mail transport when scheduled reports or email notifications are enabled

## Fresh local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan optimize:clear
php artisan test
```

Development normally uses three terminals:

```bash
npm run dev
```

```bash
php artisan serve
```

```bash
php artisan schedule:work
```

Open `http://127.0.0.1:8000`.

## Upgrade from v0.9.1 to v0.13

After copying the incremental v0.13 update over the existing project:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

No new Composer or npm package is required by v0.10–v0.13.

## Scheduler

Local development:

```bash
php artisan schedule:work
```

Typical Linux production cron entry:

```cron
* * * * * cd /path/to/flowmanager && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled work includes heartbeat, reminders, automation rules, verified backups and due scheduled reports.

Useful manual commands include:

```bash
php artisan flowmanager:heartbeat
php artisan flowmanager:reminders
php artisan flowmanager:automations
php artisan flowmanager:backup --verify
php artisan flowmanager:scheduled-reports
```

## API

Bearer tokens are created from **Administration → API tokens** and store only the token hash.

Standard resource API endpoints are read-oriented and require the `read` ability. The inbound-ticket endpoint requires `write`. This is intentionally not advertised as an unrestricted CRUD API.

Outbound webhook requests contain an HMAC SHA-256 signature in `X-FlowManager-Signature` and are recorded in the delivery log.

## Tests

```bash
php artisan test
```

The feature suite contains **123 declared tests** after v0.13.

Useful focused runs:

```bash
php artisan test --filter=ExtensibilityIntegrationTest
php artisan test --filter=DataExperienceTest
php artisan test --filter=AdvancedProjectManagementTest
php artisan test --filter=AutomationWorkflowTest
php artisan test --filter=ProductionReadinessTest
```

## Access model

| Role | Typical access |
| --- | --- |
| Administrator | Full operational, integration, document, security, system and administration access |
| Manager | Operational management, collaboration, planning, imports, documents and analytics |
| Operator | Daily CRM/project/support work, collaboration, document viewing and analytics |
| Viewer | Read-only operational, planning, document and analytics access |

Permissions remain database-backed and configurable through Roles.

## Development workflow

Development is performed directly on `main`.

```bash
php artisan test
git status
git add .
git commit -m "Describe the change"
git push origin main
```

See `docs/DEVELOPMENT_COMMANDS.md`, `docs/V09_OPERATIONS.md` and `docs/V013_FEATURES.md` for additional implementation and operational notes.
