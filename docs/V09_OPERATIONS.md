# FlowManager v0.9 operations

## Upgrade an existing v0.6.1 installation

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

Optional local demo enrichment:

```bash
php artisan db:seed --class=V09DemoSeeder
```

## Local development processes

Terminal 1:

```bash
npm run dev
```

Terminal 2:

```bash
php artisan serve
```

Terminal 3 for scheduler-driven functionality:

```bash
php artisan schedule:work
```

## Production scheduler

Run Laravel's scheduler once per minute. Example Linux cron:

```cron
* * * * * cd /path/to/flowmanager && php artisan schedule:run >> /dev/null 2>&1
```

The application schedule runs:

- heartbeat every minute;
- reminders hourly;
- automation rules every 15 minutes;
- verified backups daily at 02:15.

## Manual operational commands

```bash
php artisan flowmanager:heartbeat
php artisan flowmanager:reminders
php artisan flowmanager:automations
php artisan flowmanager:backup --verify
```

Restore a backup:

```bash
php artisan flowmanager:restore flowmanager-YYYYMMDD-HHMMSS-xxxxxx.json --force
```

Restore is intentionally command-line only. Back up the current environment before restoring another snapshot.

## Security switches

```env
FLOWMANAGER_2FA_REQUIRED_FOR_ADMINS=false
FLOWMANAGER_REQUIRE_EMAIL_VERIFICATION=false
FLOWMANAGER_MAIL_NOTIFICATIONS=false
```

Enable mandatory controls only after validating the relevant account and mail configuration. Mandatory email verification requires a working Laravel mailer.

## SLA defaults

```env
FLOWMANAGER_SLA_URGENT_HOURS=4
FLOWMANAGER_SLA_HIGH_HOURS=8
FLOWMANAGER_SLA_MEDIUM_HOURS=24
FLOWMANAGER_SLA_LOW_HOURS=48
```

Changing these values affects newly calculated SLA deadlines. Existing `sla_due_at` values are not retroactively rewritten automatically.

## Backups

Retention:

```env
FLOWMANAGER_BACKUPS_KEEP=14
```

Backups contain database rows plus the private `flowmanager/attachments` snapshot. They do not replace a full infrastructure backup strategy for production servers.

## Queue

FlowManager v0.9 does not require a queue worker for its core automation scheduler because the current FlowManager notifications can be dispatched synchronously. Laravel queue tables and System Health queue counters remain available for future asynchronous jobs.
