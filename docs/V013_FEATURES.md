# FlowManager v0.13 feature notes

## Upgrade

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan optimize:clear
php artisan test
```

No new Composer or npm package is required.

## CSV / XLSX import

Imports support Companies, Contacts, Projects, Tasks and Tickets through an upload → preview → column mapping → validation/import flow.

CSV works directly. XLSX parsing uses PHP `ZipArchive`, so verify the extension when XLSX import is required:

```bash
php -m | grep -i zip
```

## API tokens

Tokens are shown once when generated and stored as hashes. Use them as:

```text
Authorization: Bearer <token>
```

The standard resource API requires `read`; inbound Ticket creation requires `write`.

## Webhooks

Outbound events are JSON POST requests. FlowManager signs the exact payload using HMAC SHA-256 and sends the signature in:

```text
X-FlowManager-Signature
```

Delivery attempts are persisted for administration/debugging.

## Documents

Attachments remain private. Versioning creates a new Attachment record linked to the previous version. Approval/rejection records approver and timestamp and is included in audit history.

Templates support model placeholders, including nested relationships. PDF output is native; Word output is HTML packaged as a Word-compatible `.doc`, not OOXML `.docx`.

## Scheduled BI reports

The scheduler evaluates due report schedules and emails CSV output using the configured Laravel mail transport.

Run locally with:

```bash
php artisan schedule:work
```

Or execute only scheduled reports manually:

```bash
php artisan flowmanager:scheduled-reports
```

Without a working mail transport, the schedule records the error and remains available for retry rather than silently reporting success.
