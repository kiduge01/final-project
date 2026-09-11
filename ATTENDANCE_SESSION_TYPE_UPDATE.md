# Attendance Type / Sermon Update

Attendance remains linked to a registered Event / Ibada. A separate **Type** field now distinguishes multiple attendance sessions under the same event.

Supported Type values:
- Main / Single Service
- First Sermon
- Second Sermon
- Third Sermon
- Other Session

Example: `Sunday Worship — 13 Sep 2026` can have two different attendance records: `First Sermon` and `Second Sermon`.

The Church Assistant guided attendance workflow also asks for the sermon/session Type before collecting attendance counts, and AI attendance answers distinguish these sessions when multiple records exist for the same Event / Ibada.

Existing installations should run:
`database/migrations/2026_09_09_002_attendance_session_type.sql`
