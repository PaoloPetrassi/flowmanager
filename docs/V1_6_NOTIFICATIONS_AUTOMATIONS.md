# FlowManager v1.6 — Notifications & Automation 2.0

v1.6 extends the existing Notification Center and Automation Engine with per-user preferences, richer rule controls and safer repeated execution.

## Notification Center

Notifications are categorised as:

- assignments;
- comments;
- reminders;
- automations;
- system notifications.

The Notification Center supports status/category filtering, individual deletion, marking all as read and clearing read history.

### Per-user notification preferences

Preferences now contain an in-app/email matrix for every category. In-app channels can be disabled independently. Email choices are honoured only when `FLOWMANAGER_MAIL_NOTIFICATIONS=true` is enabled globally.

## Automation 2.0

### Triggers

Existing triggers remain available and the engine adds:

- unassigned Task;
- unassigned Ticket;
- overdue Project.

### Actions

Rules can now also:

- assign a selected user to a Task, Ticket or Project;
- set Task priority;
- continue to notify assignees/managers/specific users;
- continue to set Ticket priority.

### Cooldown

Every rule has a per-subject cooldown from 15 minutes to 7 days. A successful execution prevents the same rule from repeatedly acting on the same record until the cooldown expires. Failed executions are not locked out by the cooldown.

### Preview and manual controls

Managers can:

- preview matched and currently eligible records without modifying data;
- run one active rule immediately;
- run all active rules;
- pause or resume an individual rule;
- inspect the five most recent executions for each rule.

The existing scheduler continues to run active automation rules automatically.

## Database migration

v1.6 adds:

- `user_preferences.notification_preferences`;
- `automation_rules.cooldown_minutes` (default: 1440 minutes).

Run:

```bash
php artisan optimize:clear
php artisan migrate
php artisan test
```

No new Composer or npm dependency is introduced.
