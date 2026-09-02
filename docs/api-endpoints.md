# Church Management System REST API (v1)

Base URL: /api/v1
Auth: Laravel Sanctum token (Bearer)

## Auth
- POST /auth/login

## Dashboard
- GET /dashboard/stats

## Members & Groups
- GET /members
- POST /members
- GET /members/{id}
- PUT /members/{id}
- DELETE /members/{id}
- GET /groups
- POST /groups
- GET /groups/{id}
- PUT /groups/{id}
- DELETE /groups/{id}
- POST /members/{memberId}/groups/{groupId}

## Attendance
- GET /attendance/services?date_from=&date_to=&service_id=
- POST /attendance/services
- GET /attendance/events?event_id=&status=
- POST /attendance/events

## Events
- GET /events
- POST /events
- GET /events/{id}
- PUT /events/{id}
- DELETE /events/{id}
- POST /events/{id}/tasks
- POST /events/{id}/budget-items
- GET /events/{id}/report

## Finance
- GET /finance/entries?date_from=&date_to=&category=&source_type=
- POST /finance/entries
- GET /finance/entries/{id}
- PUT /finance/entries/{id}
- DELETE /finance/entries/{id}
- GET /finance/reports/daily?date=
- GET /finance/reports/monthly?month=YYYY-MM
- GET /finance/reports/yearly?year=YYYY
- GET /finance/reports/export?format=pdf|excel&period=daily|monthly|yearly

## Procurement
- GET /procurement/requests
- POST /procurement/requests
- GET /procurement/requests/{id}
- PUT /procurement/requests/{id}
- DELETE /procurement/requests/{id}
- POST /procurement/requests/{id}/submit
- POST /procurement/requests/{id}/approve
- POST /procurement/requests/{id}/reject
- POST /procurement/requests/{id}/purchase-order

## Assets
- GET /assets
- POST /assets
- GET /assets/{id}
- PUT /assets/{id}
- DELETE /assets/{id}
- POST /assets/{id}/assign
- POST /assets/{id}/maintenance

## Communication
- POST /communication/sms/broadcast
- POST /communication/sms/reminder

## Reports
- GET /reports/financial
- GET /reports/attendance
- GET /reports/events
- GET /reports/procurement
- GET /reports/assets

## Role-Based Access Policy
- Admin: all endpoints.
- Finance Officer: finance, procurement review, reports.
- Secretary: members, attendance, events, communication, reports.
- Standard User: limited read and assigned operational actions.

## Core Response Standard
Success
{
  "success": true,
  "message": "Operation completed",
  "data": {}
}

Validation Error
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["Field is required"]
  }
}
