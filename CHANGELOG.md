# FlowManager Changelog

## v0.9 — Production, Automation & Advanced Project Management

### v0.7 — Production Readiness

- Password reset flow and optional email verification.
- Native TOTP two-factor authentication with optional mandatory Administrator 2FA.
- Security page for password, 2FA and active database sessions.
- Login success/failure/lockout activity and login throttling.
- Last-login timestamp/IP tracking.
- FlowManager error pages for 403, 404, 419 and 500 responses.
- System Health administration page.
- Scheduler heartbeat and status visibility.
- Portable application-level database backup, private attachment snapshot, verification, retention and CLI restore.
- Performance indexes for common project/task/ticket/audit queries.

### v0.8 — Automation & Workflow

- Priority-based Ticket SLA due dates, reminders, breach tracking and first-response timestamp.
- Daily, weekly and monthly recurring tasks with interval/end-date support.
- Central task workflow observer so recurrence and dependency rules also apply to Kanban/quick actions.
- Task dependencies with completion protection.
- Automation rule engine with execution history.
- Automation triggers for overdue tasks, upcoming tasks, breached SLA and upcoming projects.
- Automation actions for assignee/manager/specific-user notification and SLA ticket priority changes.
- Scheduled reminder and automation Artisan commands.
- Optional email delivery for FlowManager notifications.

### v0.9 — Advanced Project Management

- Project teams with project-specific roles.
- Milestones and milestone completion.
- Subtasks and nested task hierarchy.
- Circular hierarchy/dependency validation.
- Time estimates and time tracking with live timers and manual entries.
- Automatic or manually overridden project progress.
- Project templates and instantiation.
- Full project duplication including team, milestones, task hierarchy and dependency topology.
- Gantt planning view.
- Permission-controlled team workload view.
- Operational-query isolation so templates do not affect dashboard, reports, reminders, automation or planning statistics.
- v0.9 demo seeder for local/testing installations.

## v0.6 — Analytics & Planning

- Advanced dashboard analytics with throughput, task status, ticket priority and team workload views.
- Unified monthly calendar for project deadlines, task deadlines and asset warranty expiry.
- Task and Ticket Kanban boards with policy-protected status updates and drag-and-drop enhancement.
- Reports hub with CSV, SpreadsheetML, native PDF and print output.

## v0.5 — Collaboration & Audit

- Automatic audit trail for operational resources, users and roles.
- Global Activity Log with search and filters.
- Polymorphic comments and private attachments.
- Database notification center.
- Permission-aware global search.
- Trash, restore and protected permanent deletion.
