# FlowManager

FlowManager is a Laravel management application combining CRM, project management, support, assets, collaboration, automation, document management, integrations and business intelligence in a responsive bilingual interface.

Current application version: **v1.0.1 — Polish & Release Hardening (local release)**.

The v1.0.1 release is intentionally validated and operated locally for now. The application is structured so a later production deployment does not require an architectural rewrite, but no automatic or remote deployment is enabled in this repository.

## Main capabilities

- **Dashboard** — operational KPIs, personal queues, throughput, workload and status analytics.
- **Companies / Contacts** — CRM registry, relations, tags and custom fields.
- **Projects / Tasks** — teams, milestones, subtasks, dependencies, recurring work, estimates, time tracking, templates, Gantt and workload.
- **Assets / Tickets** — inventory lifecycle, assignments, support workflow and SLA tracking.
- **Collaboration** — comments, private attachments, notifications, audit history and trash/restore.
- **Planning** — calendar, iCalendar export, Kanban, Gantt and workload.
- **Automation** — reminders, recurring tasks, SLA escalation and configurable automation rules.
- **Documents** — registry, categories, expiry, versions, approval/rejection and document templates.
- **Analytics** — KPIs, saved reports and scheduled CSV reports.
- **Integrations** — CSV/XLSX imports, scoped API tokens, inbound ticket API and signed webhooks.
- **Administration** — users, roles, extensibility, security, backups, System Health and Background Jobs.

## v1.0.1 — Polish & Release Hardening

v1.0.1 freezes the v1.0 feature set and focuses on local release quality:

- keyboard skip navigation and stronger application landmarks;
- accessible live regions for success/error feedback;
- a fully keyboard-navigable Command Palette with focus restoration and role-aware commands;
- visible focus treatment and reduced-motion support;
- local slow-query-budget diagnostics for performance QA;
- additional dark/system-theme consistency across tables, Kanban, Gantt and planning surfaces;
- a route-cacheable Home controller instead of a route action closure;
- `flowmanager:demo-reset` for a reproducible local demo database;
- `flowmanager:release-check` for non-deploying cache/readiness validation;
- `composer release:check` as the complete local release rehearsal;
- an expanded GitHub Actions release-readiness step;
- a manual QA matrix for roles, responsiveness, appearance and accessibility.

No production deployment is performed by any v1.0.1 command or workflow.

Detailed QA instructions are in [`docs/V1_0_1_QA_CHECKLIST.md`](docs/V1_0_1_QA_CHECKLIST.md).

## v1.0 — Production & Quality

v1.0 focuses on reliability and maintainability rather than adding another business module.

### Background processing

Long-running or network-bound operations now use Laravel Queue:

- guided imports;
- web-triggered backups;
- outbound webhook deliveries;
- scheduled report deliveries;
- queue-worker heartbeat checks.

A new **Administration → Background jobs** workspace shows:

- pending / processing / completed / failed tracked jobs;
- progress and attempt count;
- queue name and initiating user;
- Laravel failed jobs;
- retry and forget operations for failed jobs;
- cleanup of old completed tracking records.

Local queue connection remains configurable through normal Laravel environment settings. The default project configuration uses the database queue.

### System Health 2.0

System Health now reports:

- FlowManager version;
- PHP and Laravel versions;
- application environment and debug mode;
- database connectivity and latency;
- storage writability and disk usage;
- scheduler heartbeat;
- queue-worker heartbeat;
- current queue connection;
- pending/tracked/failed job counts;
- active sessions;
- XLSX support;
- verified backup inventory;
- recent authentication activity.

The CLI diagnostic command is:

```bash
php artisan flowmanager:doctor
```

For CI environments, runtime worker heartbeats are intentionally skipped:

```bash
php artisan flowmanager:doctor --ci
```

### Security hardening

Every web response receives baseline security headers:

- `X-Content-Type-Options: nosniff`;
- `X-Frame-Options: SAMEORIGIN`;
- strict referrer policy;
- restrictive browser permissions policy.

Existing security features remain available:

- password reset;
- optional email verification;
- TOTP 2FA;
- session management;
- login history;
- failed-login tracking;
- rate limiting;
- permission-based RBAC.

### Backup coverage

Portable FlowManager backups now include the application data introduced through v0.13 as well as private attachments, including:

- tags and custom fields;
- user preferences and saved filters;
- API tokens and webhooks;
- import history;
- document templates;
- saved/scheduled reports;
- background-job history.

Restore remains CLI-only by design.

### Quality automation

The repository contains a non-deploying GitHub Actions workflow for pushes and pull requests to `main`.

It validates:

- Composer configuration;
- PHP dependency installation;
- Node dependency installation;
- migrations on SQLite;
- `flowmanager:doctor --ci`;
- PHP syntax;
- Laravel Pint formatting;
- Pest feature tests;
- Vite production build;
- route registration.

There is **no deployment step** in the v1.0 workflow.

Local quality commands:

```bash
composer lint
composer doctor
composer quality
composer release:check
```

`composer quality` runs the normal local gate: cache cleanup, PHP syntax, Pint, Pest, frontend build and FlowManager diagnostics. `composer release:check` adds cacheability and release-readiness validation.

## Requirements

- PHP 8.3+
- Composer
- Node.js 22+ recommended
- npm
- MySQL/MariaDB or another Laravel-supported database
- PHP `zip` + SimpleXML when importing XLSX files
- a configured Laravel mail transport when email notifications or scheduled reports are enabled

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

Open `http://127.0.0.1:8000` after starting the runtime processes below.

## Local runtime

Full v1.0 functionality is easiest to test with four terminals.

### Terminal 1 — Vite

```bash
npm run dev
```

### Terminal 2 — Laravel web server

```bash
php artisan serve
```

### Terminal 3 — Scheduler

```bash
php artisan schedule:work
```

### Terminal 4 — Queue worker

```bash
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```

The queue worker is required for background imports, web backups, webhook delivery, scheduled reports and the queue-worker health heartbeat.

After changing application code, restart `queue:work` because queue workers are long-lived processes.

## Upgrade from v0.13 to v1.0

After copying the incremental v1.0 update over the existing project:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

No new Composer or npm dependency is introduced by v1.0.

Then start/restart:

```bash
npm run dev
php artisan serve
php artisan schedule:work
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```

Run the local readiness check:

```bash
php artisan flowmanager:doctor
```

## Scheduler

The local scheduler runs:

- scheduler heartbeat every minute;
- queue-worker heartbeat dispatch every minute;
- reminders hourly;
- automations every 15 minutes;
- queued backup daily at 02:15;
- scheduled-report discovery hourly;
- queue batch, failed-job and FlowManager background-history pruning daily.

Useful manual commands:

```bash
php artisan flowmanager:heartbeat
php artisan flowmanager:reminders
php artisan flowmanager:automations
php artisan flowmanager:backup --verify
php artisan flowmanager:queue-backup
php artisan flowmanager:scheduled-reports
php artisan flowmanager:doctor
php artisan flowmanager:prune-jobs
php artisan flowmanager:release-check --ci
php artisan flowmanager:demo-reset
php artisan schedule:list
```

## XLSX support check

On Windows / Git Bash, avoid piping `php -m` if the local PHP executable produces TTY errors. Use:

```bash
php -r "echo class_exists('ZipArchive') ? 'ZipArchive OK'.PHP_EOL : 'ZipArchive MISSING'.PHP_EOL;"
```

and:

```bash
php --ri zip
```

CSV imports do not require `ZipArchive`.

## Tests

```bash
php artisan test
```

The v1.0 baseline contains **133 passing tests**. v1.0.1 adds five polish/hardening checks for **138 declared feature tests**, including dedicated production-quality and background-job coverage.

Useful focused runs:

```bash
php artisan test --filter=V100ProductionQualityTest
php artisan test --filter=ProductionReadinessTest
php artisan test --filter=SecurityFeaturesTest
php artisan test --filter=ExtensibilityIntegrationTest
php artisan test --filter=AdvancedProjectManagementTest
```

## Development workflow

Development is performed directly on `main`.

```bash
composer quality
git status
git add .
git commit -m "Describe the change"
git push origin main
```

A push to `main` starts the GitHub Actions quality pipeline, but **does not deploy FlowManager anywhere**.

## Production deployment

Production deployment is intentionally postponed after v1.0. No credentials, server configuration or automatic deployment action is included.

When deployment is requested, use `docs/V1_LOCAL_RELEASE.md` as the validated local baseline and create a separate production deployment plan for the chosen server/environment.

## Documentation

- `docs/DEVELOPMENT_COMMANDS.md`
- `docs/V09_OPERATIONS.md`
- `docs/V013_FEATURES.md`
- `docs/V1_LOCAL_RELEASE.md`
