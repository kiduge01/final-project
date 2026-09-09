# Corrected Whole-System Workflow

## Shared architecture
Web UI/API -> Authentication/RBAC/CSRF -> Controllers -> Shared Services -> PDO/MySQL -> Audit -> Response.

Dashboard, reports and AI now share the same `StatisticsService`, `AttendanceService` and `ReportService`, removing conflicting totals produced by separate SQL calculations.

## Members
Register/update -> validate -> duplicate constraints -> persist -> audit -> available to attendance/giving/communication/reports.

## Guests
Register guest -> visit/service date -> attendance context -> follow-up date -> follow-up queue -> communication -> status/history.

## Attendance
Select service name/date -> record category counts -> server computes total -> persist snapshot -> audit -> shared statistics -> dashboard/reports/AI.
A separate event table is not required for attendance reporting.

## Church Giving
Select active income category -> positive amount -> payment method -> record -> approval state where schema supports it -> audit -> reporting/AI.
Event, procurement and supplier linkage is forced off for new giving records.

## Assets
Register -> location/condition -> optional user or location assignment -> maintenance -> condition update -> audit -> reports.
Department/event assignments are rejected by the API.

## Communication
System keeps message history through the existing message/SMS subsystem. `GET /api/v1/guests/follow-up` provides a controlled queue of guests whose follow-up date is due.

## AI
Authentication/permission -> intent -> date/context resolution -> service selection -> controlled tool call -> result validation -> grounded response -> audit log.
AI never executes arbitrary SQL and receives only validated application data.

## Scope guard
Requests to legacy department, procurement, standalone event, Bezaleli or integration API prefixes return HTTP 410.
