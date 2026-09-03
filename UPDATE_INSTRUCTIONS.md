# FlowManager v1.4–v1.6 Update Instructions

This incremental update expects a working **FlowManager v1.3.0 + Hotfix 1** installation.

## 1. Copy the update

Extract the ZIP into the FlowManager project root and allow existing files to be overwritten.

Example project root on Windows:

```text
C:\Projects\flowmanager
```

## 2. Apply the database migration and clear caches

From the VS Code integrated **Git Bash** terminal:

```bash
php artisan optimize:clear
php artisan migrate
```

v1.6 adds notification preferences to `user_preferences` and per-rule cooldowns to `automation_rules`.

## 3. Run the quality checks

```bash
php artisan test
npm run build
php artisan flowmanager:doctor
```

The v1.6.0 suite contains **162 feature tests**. No new Composer or npm package is required by v1.4–v1.6.

## 4. Restart long-lived local processes

If the scheduler or queue worker is already running, stop and restart it so it loads the new v1.6 code:

```bash
php artisan schedule:work
```

```bash
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```

Keep Vite and the Laravel development server running as usual when needed:

```bash
npm run dev
php artisan serve
```

## 5. Optional release package

After local validation, a clean source package can be generated with:

```bash
php artisan flowmanager:package-release
```

or:

```bash
composer release:package
```

No deployment is performed by these commands.
