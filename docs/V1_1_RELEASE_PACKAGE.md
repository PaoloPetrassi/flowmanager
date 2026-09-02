# FlowManager v1.1 — Clean Release Package

v1.1 adds a reproducible source-package workflow for sharing or archiving FlowManager without leaking local state.

## Build the clean package

From the project root:

```bash
npm run build
php artisan flowmanager:package-release
```

or through Composer:

```bash
composer release:package
```

The default output is:

```text
dist/flowmanager-v1.3.0.zip
```

A custom relative or absolute destination can be supplied:

```bash
php artisan flowmanager:package-release --output=dist/my-flowmanager.zip
```

## Included

The package contains the FlowManager application source, migrations, seeders, tests, documentation, frontend source and any compiled `public/build` assets already present.

## Excluded

The package command deliberately excludes:

- `.env` and environment-specific secret files;
- `.git` history, editor metadata and local AI-agent/tooling metadata;
- `vendor` and `node_modules`;
- the local SQLite database;
- PHPUnit cache files;
- Laravel sessions, compiled views and cache payloads;
- application logs;
- backups and private runtime files;
- the `dist` directory itself.

`.env.example` and runtime `.gitignore` placeholders remain in the package.

## Installing a clean package

After extraction:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure at least `FLOWMANAGER_ADMIN_PASSWORD`, then run:

```bash
php artisan migrate
php artisan db:seed
php artisan optimize:clear
npm run build
php artisan flowmanager:doctor --ci
```

No deployment is performed by the packaging command.
