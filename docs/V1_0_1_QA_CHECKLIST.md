# FlowManager v1.0.1 — Local QA & Release Checklist

v1.0.1 is a polish and hardening release. It does not deploy FlowManager anywhere and does not add a new business module.

## 1. Automated gate

Run from the project root:

```bash
php artisan optimize:clear
php artisan test
composer quality
```

For the complete local release rehearsal, including cacheability and the production frontend bundle:

```bash
composer release:check
```

`composer release:check` performs no deployment. It runs syntax checks, Pint, Pest, a Vite production build, FlowManager diagnostics and cacheability checks for configuration, routes and Blade views.

If a release check is interrupted while caches are being generated, restore the development state with:

```bash
php artisan optimize:clear
```

## 2. Runtime QA

Start the four local processes used by v1.0:

```bash
npm run dev
php artisan serve
php artisan schedule:work
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```

Then run:

```bash
php artisan flowmanager:doctor
```

Scheduler and queue-worker heartbeats should report `OK` after roughly one minute.

## 3. Role matrix smoke test

Use one account for each system role and verify representative actions.

| Area | Administrator | Manager | Operator | Viewer |
| --- | --- | --- | --- | --- |
| Companies / Contacts | Full | Full operational access | View/Create/Update | View |
| Projects | Full | Full operational access | View | View |
| Tasks / Tickets | Full | Full operational access | View/Create/Update | View |
| Assets | Full | Full operational access | View/Assign | View |
| Comments / Attachments | Full | Create/Delete | Create | Read through records |
| Reports / Documents / Analytics | Full | Available | Available | Available |
| Users / Roles | Full | View | No administration | No administration |
| Audit / Trash | Full | Available with restricted permanent deletion | No | No |
| Tags / Custom fields / Integrations | Full | Restricted | No | No |
| System / Background jobs | Full | No | No | No |

For each role, also check that the sidebar and `Ctrl/Cmd + K` Command Palette do not advertise actions the account cannot perform.

## 4. Accessibility smoke test

Verify the following with the keyboard only:

1. Press `Tab` immediately after loading an authenticated page: **Skip to main content** becomes visible.
2. Activate it with `Enter`: focus moves to the page content.
3. Open the mobile sidebar and close it with `Esc`.
4. Press `Ctrl+K` / `Cmd+K` to open the Command Palette.
5. In the palette, use `Up`, `Down`, `Home`, `End`, `Enter` and `Esc`.
6. Close the palette and confirm focus returns to the control that opened it.
7. Tab through forms and confirm every interactive element has a visible focus indication.
8. Enable the operating-system reduced-motion preference and verify FlowManager remains fully usable.

## 5. Responsive and appearance QA

Check at minimum:

- desktop width >= 1440 px;
- laptop width around 1280 px;
- tablet width around 768–1024 px;
- narrow mobile width around 375 px.

Repeat representative pages in Light, Dark and System themes:

- Dashboard;
- Companies index/show;
- Project show;
- Tasks index;
- Tickets index;
- Kanban;
- Gantt;
- Analytics;
- System Health.

Tables may scroll horizontally on narrow screens; columns must not overlap or become unreadable.


## 6. Performance smoke test

Local HTTP requests use a cumulative database-query budget. The default is:

```dotenv
FLOWMANAGER_SLOW_QUERY_MS=500
```

When a request crosses the threshold, FlowManager writes a warning to `storage/logs/laravel.log` containing the connection, budget, last query time and SQL template without query bindings.

Exercise Dashboard, Global Search, Company/Project detail, Analytics and System Health, then inspect the log. Treat repeated warnings as candidates for eager loading, query consolidation or indexing rather than immediately raising the threshold. Set the value to `0` to disable this local diagnostic.

## 7. Reproducible demo dataset

The local demo database can now be rebuilt with one command:

```bash
php artisan flowmanager:demo-reset
```

This command runs only in `local` or `testing` environments and permanently deletes the current local database contents before recreating and seeding them.

For unattended local use:

```bash
php artisan flowmanager:demo-reset --force
```

If `FLOWMANAGER_ADMIN_PASSWORD` is not configured:

```bash
php artisan flowmanager:demo-reset --force --admin-password="Choose-A-Local-Password"
```

Never use a demo-reset workflow for production data.

## 8. Optional production-style rehearsal — still local

This does **not** deploy FlowManager. It only validates production-oriented configuration on the local machine.

Back up `.env`, then temporarily set at least:

```dotenv
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

Build the frontend and validate:

```bash
npm run build
php artisan optimize:clear
php artisan flowmanager:release-check --ci --production --build
```

After the rehearsal, restore the normal local `.env` and run:

```bash
php artisan optimize:clear
```

## 9. Git release checkpoint

After all checks are green:

```bash
git status
git add .
git commit -m "Release FlowManager v1.0.1 polish and hardening"
git push origin main

git tag -a v1.0.1 -m "FlowManager v1.0.1"
git push origin v1.0.1
```
