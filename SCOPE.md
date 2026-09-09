# Final-Year Project Scope

## Active modules
- Member Management
- Guest Management and follow-up
- Attendance Management
- Church Giving Management
- Asset Management and maintenance
- Communication / SMS
- Reports and Dashboard
- AI-Assisted Administration
- User Management, RBAC, CSRF protection and audit logging

## Explicitly not implemented
- Department Management
- Procurement
- Standalone Event Management
- Bezaleli integration or any other external church-system integration
- Mobile application
- Online payment gateway
- Biometric/facial-recognition attendance

The application intentionally blocks API routes for the out-of-scope modules so old legacy code cannot become an active workflow accidentally.

## 2026-09-08 Workflow Amendment
Events / Ibada are included as a supporting core module because attendance must reference a registered service/event. Department Management remains out of scope. Bezaleli integration is not implemented.
