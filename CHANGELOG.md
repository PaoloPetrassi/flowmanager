# FlowManager Changelog

## v1.0.0 — Production & Quality (local release)

- Added tracked Laravel Queue processing for imports, web-triggered backups, outbound webhooks and scheduled reports.
- Added Administration → Background Jobs with queue counts, progress, execution history, failed jobs, retry/forget controls and history cleanup.
- Added queue-worker heartbeat and integrated it into System Health.
- Expanded System Health with database latency, disk capacity, queue connection, tracked jobs, failed jobs, version and XLSX support.
- Added `flowmanager:doctor` local/CI readiness diagnostics.
- Added baseline security response headers.
- Changed database/Redis queue connections to dispatch after database commit.
- Expanded portable backups to include v0.10–v0.13 application tables and v1.0 background-job history.
- Added indexes for notification unread checks, import history, webhook delivery status and due scheduled reports.
- Added cross-platform PHP syntax check script and Composer `lint`, `doctor`, `quality` and `quality:fix` scripts.
- Added GitHub Actions CI for Composer validation, migration validation, diagnostics, syntax, Pint, Pest, Vite build and route integrity.
- Restored persistent Laravel runtime directories required after a clean source checkout.
- Updated local runtime documentation to include a database queue worker.
- Production deployment remains intentionally disabled/postponed.

## v0.13 — Integration, Advanced UX, Documents & Business Intelligence

### v0.10 — CRM & Integration

- Guided CSV/XLSX import with preview, mapping and validation results.
- Bulk status/priority/delete actions for operational work queues.
- Saved filters across the main registries.
- Cross-module tags and Administrator-defined custom fields.
- Scoped bearer API tokens with hashed token storage.
- Read API for supported resources and write-scoped inbound Ticket creation.
- Signed outbound webhooks and delivery history.
- iCalendar planning export.

### v0.11 — Advanced UX

- Ctrl/Cmd + K Command Palette.
- Persisted light/dark/system appearance preference.
- Comfortable/compact density preference.
- Configurable dashboard widget visibility.
- Persisted table-column visibility for Projects, Tasks and Tickets.

### v0.12 — Document Management

- Document metadata on private attachments.
- Version chains, expiry, checksum and approval/rejection workflow.
- Central Documents registry.
- Document Templates with PDF and Word-compatible DOC generation.
- Document actions integrated into operational record workspaces.

### v0.13 — Business Intelligence

- Analytics workspace with six-month throughput and status distributions.
- Tracked-hours and operational workload metrics.
- Saved analytics reports.
- Scheduled CSV reports through the configured Laravel mailer.
- Daily/weekly/monthly schedules with run/error tracking.

## v0.9 — Production, Automation & Advanced Project Management

- Password reset, optional verification, TOTP 2FA, session/login security and rate limiting.
- System Health, heartbeat, portable backup/restore and operational indexes.
- Ticket SLA, recurring tasks, task dependencies and automation engine.
- Project teams, milestones, subtasks, time tracking, templates, duplication, Gantt and workload.

## v0.6 — Analytics & Planning

- Advanced dashboard analytics, calendar, Kanban and report exports.

## v0.5 — Collaboration & Audit

- Audit trail, comments, private attachments, notifications, global search and trash/restore.
