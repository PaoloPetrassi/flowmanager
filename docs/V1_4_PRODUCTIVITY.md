# FlowManager v1.4 — Productivity

v1.4 turns the existing search, saved-filter and bulk-action foundations into day-to-day productivity features.

## Global search and Command Palette

The global search now uses one permission-aware search service for Companies, Contacts, Projects, Tasks, Assets, Tickets and Users.

- Search results are grouped by module.
- Prefix matches are prioritised where useful.
- The full search page can be restricted to one accessible module.
- `Ctrl/Cmd + K` uses the same backend and now shows record metadata and module-specific icons.
- Modules that the current user cannot view are never queried or exposed.

## Saved views

Saved filters are presented as **Saved views** on the six main registries.

- Companies
- Contacts
- Projects
- Tasks
- Assets
- Tickets

A saved view can be marked as the user's default for that registry. The default is applied automatically when the index is opened without explicit filters. Defaults remain private to the current user.

## Configurable page size

The same registry pages support 15, 25, 50 or 100 rows per page while preserving active filters and sorting.

## Bulk actions

Projects, Tasks, Tickets and Assets support permission-checked bulk operations for up to 100 selected records:

- status change;
- priority change where supported;
- assignment / unassignment;
- deletion.

Task and Ticket completion timestamps are preserved when status changes are applied in bulk. Asset assignment also keeps the asset lifecycle consistent.

## Upgrade notes

v1.4 introduces no Composer or npm dependency and no database migration of its own.
