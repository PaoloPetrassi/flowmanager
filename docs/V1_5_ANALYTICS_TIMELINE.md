# FlowManager v1.5 — Analytics & Activity Timeline

v1.5 makes the dashboard analytics period-aware and adds a compact operational activity timeline.

## Analytics periods

The dashboard supports:

- last 7 days;
- last 30 days;
- last 90 days;
- year to date;
- a custom range up to 366 days.

The selected range drives the throughput chart and period KPIs. Invalid custom ranges safely fall back to the default 30-day period.

## Period KPIs

Depending on the user's permissions, the dashboard can show:

- completed Tasks;
- resolved Tickets;
- average Ticket resolution time;
- Tickets resolved after their SLA deadline;
- completed Projects.

The throughput chart automatically switches between daily, weekly and monthly buckets based on the selected range.

## Activity Timeline

Users with the `audit.view` permission can enable the **Activity timeline** dashboard widget. It displays recent tracked changes within the selected analytics period and links back to the affected record when a route is available.

The widget can be enabled or disabled together with the other dashboard components from Preferences.

## Upgrade notes

v1.5 introduces no Composer or npm dependency and no database migration of its own.
