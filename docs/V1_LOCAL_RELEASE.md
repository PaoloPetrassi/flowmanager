# FlowManager v1.0 — Local Release Guide

FlowManager v1.0 is the first release treated as a release candidate rather than a feature bundle. It is intentionally kept local for now; remote deployment is postponed.

## Upgrade checklist from v0.13

1. Stop `npm run dev`, `php artisan serve`, `php artisan schedule:work` and any existing queue worker.
2. Copy the v1.0 incremental update over the existing project.
3. Run:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

4. Run the readiness diagnostic:

```bash
php artisan flowmanager:doctor --ci
```

5. Start the four local runtime processes:

```bash
npm run dev
```

```bash
php artisan serve
```

```bash
php artisan schedule:work
```

```bash
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```

6. Wait roughly one minute, then run:

```bash
php artisan flowmanager:doctor
```

Scheduler and Queue Worker should now both report a recent heartbeat.

## Background jobs

Open:

`Administration → Background jobs`

Test at least these local flows:

- queue a database backup from System Health;
- import a small CSV file;
- configure a local/mock webhook if desired;
- run `php artisan flowmanager:scheduled-reports` with a due report.

The page should show progression from `Pending` to `Processing` to `Completed`.

If a queued job fails after all retry attempts, it appears in the Laravel failed-jobs section and can be retried by an Administrator.

## Queue worker notes

Laravel queue workers are long-lived. After editing PHP classes used by jobs, restart the worker:

```bash
Ctrl+C
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```

To process a single job while debugging:

```bash
php artisan queue:work --once --queue=system,imports,reports,webhooks,default
```

Useful queue commands:

```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
php artisan flowmanager:prune-jobs
```

Use destructive queue commands carefully.

## Local release gate

Before pushing a v1.0 change:

```bash
composer quality
```

Equivalent manual sequence:

```bash
php artisan optimize:clear
composer lint
php vendor/bin/pint --test
php artisan test
npm run build
```

Then:

```bash
git status
git add .
git commit -m "Describe the change"
git push origin main
```

GitHub Actions repeats the quality checks. It does not deploy.

## Backup verification

Manual verified backup:

```bash
php artisan flowmanager:backup --verify
```

Web-created backups are queued and can be observed in Background Jobs.

Restore remains intentionally CLI-only:

```bash
php artisan flowmanager:restore flowmanager-YYYYMMDD-HHMMSS.json --force
```

Always validate restore behavior on non-production data first.

## Deployment status

The following are intentionally postponed:

- VPS/hosting selection;
- DNS/domain;
- TLS/HTTPS termination;
- Nginx/Apache/PHP-FPM configuration;
- Supervisor/systemd queue workers;
- production cron;
- production database credentials;
- GitHub deployment secrets;
- automatic deployment.

The local v1.0 baseline should remain green before any future deployment work begins.
