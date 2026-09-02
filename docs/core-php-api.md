# Core PHP API Quick Guide

Base URL: /api/v1
Session auth is required for protected endpoints.

## Public
- POST /api/v1/auth/login
  - body: { "phone": "+2557...", "password": "..." }

## Protected
- GET /api/v1/dashboard/stats
- GET /api/v1/members
- POST /api/v1/members
- POST /api/v1/finance/entries
- GET /api/v1/events/{id}/report

## Response Format
{
  "success": true,
  "message": "...",
  "data": {}
}

## Notes
- Full route inventory remains documented in docs/api-endpoints.md.
- The current core API includes the high-use routes fully wired for quick deployment.
- Other module routes can be added in public/index.php following the same pattern.
