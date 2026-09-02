# FlowManager Changelog

## v1.3.0 — REST/OpenAPI & Webhooks (local release)

- Added authenticated in-app API documentation and a public machine-readable OpenAPI 3.1 JSON specification.
- Documented the read API, inbound Ticket endpoint, bearer-token abilities, permissions, pagination and response codes.
- Added webhook pause/resume and queued test-delivery actions.
- Added recent webhook delivery status and delivery identifiers to the administration UI.
- Added UUID delivery IDs, delivery timestamps and HMAC-SHA256 signatures over `timestamp.rawBody`.
- Added focused feature coverage for OpenAPI documentation and webhook delivery behavior.
- Production deployment remains intentionally postponed.

## v1.2.0 — Demo Mode (local release)

- Added a dedicated seeded demo account with configurable identity and role.
- Added one-click demo login from the guest sign-in page.
- Added a persistent demo-mode banner in the authenticated application.
- Added server-side read-only protection for state-changing demo requests while preserving harmless UI preferences and notification state.
- Extended `flowmanager:demo-reset` with demo-account reporting and an optional temporary demo password.
- Added focused feature coverage for demo authentication, access and write protection.

## v1.1.0 — Clean Release Package (local release)

- Added `flowmanager:package-release` and `composer release:package` to create sanitized source ZIP archives.
- Release packages exclude `.env`, Git metadata, dependencies, local databases, logs, sessions, caches, backups and private runtime files.
- Compiled `public/build` assets are retained when available.
- Added dedicated clean-package documentation.

## v1.0.1 — Polish & Release Hardening (local release)

- Added skip navigation, explicit navigation landmarks and accessible flash-message live regions.
- Upgraded the Command Palette with role-aware default commands, arrow/Home/End/Enter navigation, focus trapping and focus restoration.
- Added consistent `:focus-visible` treatment and reduced-motion support.
- Added a local-only cumulative database query budget warning (`FLOWMANAGER_SLOW_QUERY_MS`) for performance QA without logging query bindings.
- Improved dark/system-theme consistency across tables, Kanban, Gantt, planning cards and header controls.
- Replaced the Home route action closure with a cacheable invokable controller.
- Made the displayed FlowManager release version code-owned so stale local `.env` values cannot mask an application upgrade.
- Added `flowmanager:demo-reset` for a reproducible local seeded environment, guarded against non-local execution.
- Added `flowmanager:release-check` for doctor, config/route/view cacheability and optional production/build checks.
- Added `composer release:check` and strengthened `composer quality` with FlowManager diagnostics.
- Extended GitHub Actions with the same release-readiness validation without adding deployment.
- Added a role/accessibility/responsive/manual QA checklist.
- Corrected the documented v1.0 baseline from 134 to 133 passing tests; v1.0.1 declares 138 feature tests.
- Production deployment remains intentionally postponed.

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
