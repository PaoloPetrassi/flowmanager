# FlowManager Changelog

## v0.6 — Analytics & Planning

- Advanced dashboard analytics with six-month throughput, task status, ticket priority and team workload views.
- Unified monthly calendar for project deadlines, task deadlines and asset warranty expiry.
- Task and Ticket Kanban boards with policy-protected status updates and drag-and-drop enhancement.
- Reports hub for Projects, Tasks, Tickets and Assets.
- UTF-8 CSV exports.
- Excel-compatible SpreadsheetML exports.
- Native downloadable PDF report generation without an additional Composer dependency.
- Print-friendly report view.
- Responsive styling for analytics, calendar and Kanban surfaces.
- New report/planning/dashboard feature tests.

## v0.5 — Collaboration & Audit

- Automatic audit trail for operational resources, users and roles.
- Global Activity Log with search and filters.
- Polymorphic comments on Companies, Contacts, Projects, Tasks, Assets and Tickets.
- Private authenticated attachments on the same operational records.
- Database notification center with unread badge and assignment/comment notifications.
- Permission-aware global search.
- Trash, restore and protected permanent deletion for soft-deleted operational records.
- New audit, comments, attachments and notifications database migrations.
- New collaboration, audit and trash permissions.
- Feature tests for all v0.5 subsystems.

## Upgrade commands from v0.4.x

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

No v0.6-specific migration is required. The migration command applies the four v0.5 tables.
