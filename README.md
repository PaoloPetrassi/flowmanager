# FlowManager

FlowManager is a Laravel management application that combines CRM, projects, tasks, assets, support, collaboration, auditability, workflow automation, planning and administration in a responsive bilingual interface.

Current application version: **v0.9 — Production, Automation & Advanced Project Management**.

## Core modules

- **Dashboard** — operational KPIs, personal queues, throughput, workload and status analytics.
- **Companies / Contacts** — CRM registry and cross-module relationships.
- **Projects** — companies, contacts, managers, team members, milestones, templates, progress and planning.
- **Tasks** — assignments, subtasks, dependencies, recurring tasks, estimates, time tracking and deadlines.
- **Assets** — inventory, lifecycle, warranty and assignment.
- **Tickets** — support workflow, priorities, SLA tracking and assignments.
- **Collaboration** — comments and private authenticated attachments.
- **Activity / Audit** — before/after history for relevant operations.
- **Notifications** — in-app alerts for assignments, comments, reminders and workflow events.
- **Planning** — calendar, Kanban, Gantt and workload views.
- **Automations** — rule-based reminders, notifications, escalations and SLA actions.
- **Reports** — CSV, SpreadsheetML Excel, native PDF and print views.
- **Administration** — users, roles, trash/restore and system health.

## v0.7 — Production Readiness

### Authentication and account security

- password reset flow;
- optional email verification;
- TOTP two-factor authentication without an external package;
- optional mandatory 2FA for Administrators;
- password change from the Security page;
- active database-session listing and termination;
- login success/failure/lockout history;
- login rate limiting;
- last-login timestamp and IP;
- custom FlowManager 403, 404, 419 and 500 pages.

The sensitive TOTP secret is encrypted at rest and excluded from audit values.

### System Health

Administrators can open **Administration → System** to inspect:

- application environment and debug state;
- database connectivity;
- writable storage;
- scheduler heartbeat;
- queued and failed jobs;
- active sessions;
- available database backups;
- recent login activity.

### Portable backup / restore

FlowManager provides application-level database backups that do not require `mysqldump`. The backup manifest includes the FlowManager tables and a snapshot of private collaboration attachments.

Create and verify a backup:

```bash
php artisan flowmanager:backup --verify
```

Restore is intentionally CLI-only:

```bash
php artisan flowmanager:restore flowmanager-YYYYMMDD-HHMMSS-xxxxxx.json --force
```

The System page permits authorized creation, verification-aware download and deletion, but not browser-based restore.

## v0.8 — Automation & Workflow

### Ticket SLA

Ticket SLA deadlines are calculated from priority. Defaults are configurable through `.env`:

```env
FLOWMANAGER_SLA_URGENT_HOURS=4
FLOWMANAGER_SLA_HIGH_HOURS=8
FLOWMANAGER_SLA_MEDIUM_HOURS=24
FLOWMANAGER_SLA_LOW_HOURS=48
```

FlowManager records SLA due time, breach time, reminder state and first response.

### Recurring tasks

Tasks can recur:

- daily;
- weekly;
- monthly;
- at a configurable interval;
- optionally until an end date.

Completing a recurring task creates the next occurrence only once. The same behavior applies whether completion occurs from the edit form, quick action or Kanban.

### Dependencies and workflow protection

A task cannot be completed while one of its dependencies is still open. Circular parent hierarchies and circular dependency graphs are rejected during validation.

### Automation rules

Rules can react to:

- overdue tasks;
- tasks due soon;
- breached ticket SLA;
- projects due soon.

Actions include notifying an assignee, notifying a manager, notifying a selected user or changing ticket priority for SLA rules. Automation executions are recorded in `automation_runs`.

## v0.9 — Advanced Project Management

### Project teams and milestones

Projects support multiple team members with project-specific roles, while retaining one project manager. Milestones can be created and completed from the project workspace.

### Subtasks and dependencies

Tasks support:

- parent / child hierarchy;
- milestones;
- task-to-task dependencies;
- loop prevention;
- dependency-aware completion.

### Time tracking and estimates

Tasks and projects can store estimates. Users can:

- start and stop a timer;
- enter time manually;
- see tracked minutes on tasks and projects;
- compare tracked work against estimates.

### Project progress

Project progress can be calculated automatically from completed tasks or overridden manually when required.

### Templates and duplication

Projects can be saved as reusable templates. Template instantiation preserves task hierarchy and dependency topology while resetting operational dates and completion state. Existing projects can also be duplicated with their team, milestones, tasks and dependencies.

Templates are excluded from operational dashboards, reports, search, calendar, workload, reminders and automation queries.

### Gantt and workload

The planning area includes:

- a date-range Gantt view for project/task planning;
- team workload based on open assigned work;
- permission-controlled access to workload information.

## Localization and responsive design

FlowManager supports **English and Italian**. Locale selection is persisted in session and cookie.

The interface is desktop-first but remains usable on tablets and phones. Dense operational surfaces such as large tables, Kanban and Gantt use local horizontal scrolling rather than silently hiding information.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL/MariaDB or another Laravel-supported database

## Fresh local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`, then:

```bash
php artisan migrate
php artisan db:seed
php artisan optimize:clear
php artisan test
```

Run the application in two terminals:

```bash
npm run dev
```

```bash
php artisan serve
```

For scheduled reminders, automations, heartbeat and backups during local development, use a third terminal:

```bash
php artisan schedule:work
```

Open `http://127.0.0.1:8000`.

## Upgrade from v0.6.1 to v0.9

After copying the v0.9 update over the existing project:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

For a local/demo installation you can additionally enrich existing demo records with v0.9 data:

```bash
php artisan db:seed --class=V09DemoSeeder
```

`V09DemoSeeder` refuses to run outside `local` or `testing` environments.

No additional Composer or npm dependency is required by v0.7–v0.9.

## Scheduler

The scheduler is required for v0.8 background behavior.

Local development:

```bash
php artisan schedule:work
```

Typical Linux production cron entry:

```cron
* * * * * cd /path/to/flowmanager && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs include:

- scheduler heartbeat every minute;
- reminders hourly;
- automation rules every 15 minutes;
- verified backup every day at 02:15.

Manual execution is also available:

```bash
php artisan flowmanager:heartbeat
php artisan flowmanager:reminders
php artisan flowmanager:automations
php artisan flowmanager:backup --verify
```

## Security configuration

Important optional `.env` values:

```env
FLOWMANAGER_2FA_REQUIRED_FOR_ADMINS=false
FLOWMANAGER_REQUIRE_EMAIL_VERIFICATION=false
FLOWMANAGER_MAIL_NOTIFICATIONS=false
FLOWMANAGER_BACKUPS_KEEP=14
```

Both mandatory Administrator 2FA and mandatory email verification are **off by default**, so an upgrade does not lock existing users out. Configure a working Laravel mailer before enabling email verification or mail notifications in production.

## Tests

```bash
php artisan test
```

The feature suite contains **111 declared tests** after v0.9.

Useful focused runs:

```bash
php artisan test --filter=SecurityFeaturesTest
php artisan test --filter=ProductionReadinessTest
php artisan test --filter=AutomationWorkflowTest
php artisan test --filter=AdvancedProjectManagementTest
php artisan test --filter=PlanningToolsTest
```

## Access model

| Role | Typical access |
| --- | --- |
| Administrator | Full operational, security, system, automation, audit and administration access |
| Manager | Operational management, collaboration, planning, workload and automation access |
| Operator | Daily CRM, project/task/ticket work, time tracking and collaboration |
| Viewer | Read-only operational and planning access |

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

See `docs/DEVELOPMENT_COMMANDS.md` for historical scaffolding and `docs/V09_OPERATIONS.md` for production/scheduler/backup operations.
