# FlowManager v1.2 — Demo Mode

v1.2 adds a dedicated portfolio/demo account that can expose the complete FlowManager interface without allowing a visitor to modify business or administration data.

## Default local configuration

The `.env.example` file contains:

```dotenv
FLOWMANAGER_DEMO_ENABLED=true
FLOWMANAGER_DEMO_NAME="FlowManager Demo"
FLOWMANAGER_DEMO_EMAIL=demo@flowmanager.test
FLOWMANAGER_DEMO_PASSWORD="FlowManagerDemo!2026"
FLOWMANAGER_DEMO_ROLE=administrator
FLOWMANAGER_DEMO_READ_ONLY=true
```

When no explicit `FLOWMANAGER_DEMO_ENABLED` value is supplied, demo mode defaults to enabled only for `APP_ENV=local` and disabled for other environments.

## Database upgrade

Run:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=DemoUserSeeder
php artisan test
```

The demo user receives the configured role. The default is `administrator` so a portfolio visitor can browse the complete product surface.

## One-click demo access

When demo mode is enabled, the login screen contains **Enter demo mode**. No password needs to be typed: FlowManager authenticates the seeded account and opens the dashboard.

The account is visually identified by a persistent demo banner after login.

## Read-only protection

With:

```dotenv
FLOWMANAGER_DEMO_READ_ONLY=true
```

FlowManager blocks state-changing web requests made by the demo account. Normal browsing remains available, while harmless personal UI operations such as language, appearance/density preferences and notification read state still work.

The protection applies server-side through middleware; disabled buttons on integration pages are only an additional UI safeguard.

## Rebuild the local demo dataset

The existing local reset command now also reports the demo account when demo mode is enabled:

```bash
php artisan flowmanager:demo-reset --force --admin-password="YOUR_ADMIN_PASSWORD"
```

To force-enable demo mode for a single reset and override its password in memory:

```bash
php artisan flowmanager:demo-reset --force \
  --admin-password="YOUR_ADMIN_PASSWORD" \
  --demo-password="YOUR_DEMO_PASSWORD"
```

The reset command remains restricted to `local` and `testing` environments.
