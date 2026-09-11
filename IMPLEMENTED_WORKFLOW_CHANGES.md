# Implemented Workflow Changes

1. Added a shared service layer for date ranges, attendance, statistics, reports and AI workflow.
2. Dashboard now uses the same statistics source as reports/AI.
3. AI now resolves intent/date/service context and asks clarification for multiple same-day services instead of guessing.
4. Attendance remains a single aggregate snapshot model; totals are calculated server-side.
5. Church Giving UI was reduced to in-scope income/giving functions; new entries cannot attach to events, procurement or suppliers.
6. Assets UI was reduced to asset register/location/condition/maintenance. Department/event assignments are rejected.
7. Added a guest follow-up queue endpoint for communication workflow.
8. Settings now shows approved scope rather than department/procurement configuration.
9. Legacy department/procurement/event/integration API prefixes are blocked.
10. API 500 responses no longer expose file paths, line numbers or raw exceptions to users.
11. Bezaleli/external integration is explicitly not implemented.
