# Corrections applied — 2026-09-09

## 1. AI Agent moved into the global chatbot
- Removed the AI Agent item from the sidebar and removed the active `/ai-assistant` page route.
- The floating Church Assistant is now the single AI interface.
- It supports normal data questions and confirmed agent actions in the same conversation.
- Agent actions currently include asset registration, Event/Ibada creation, member registration, guest registration, SMS sending and email sending.
- The chatbot collects missing required information, shows a confirmation action, and only writes/sends after confirmation.
- All actions still enforce the underlying role permissions and audit trail.

## 2. Settings rebuilt as real application settings
Settings now cover:
- My account/profile
- Change password
- System users CRUD and activation/deactivation
- Roles and permission editing
- Church/system identity and defaults
- SMS/email identity settings (secrets are intentionally not exposed in the browser)
- Security/session preferences
- Audit log

## 3. Professional reporting corrections
- Removed browser-print as the report generation mechanism.
- Added real server-generated `.pdf` downloads.
- Reports support week, month, quarter, year and custom date ranges.
- The report preparer can add a human description/remarks field.
- Current report data is automatically compared with the immediately preceding equal-length period.
- Attendance, average attendance, guest visits, giving and new-member changes are calculated by PHP/MySQL.
- The AI receives those validated comparison figures and describes the trend. If an OpenAI API key is not configured, the system uses a deterministic grounded comparison narrative.
- The PDF includes reporting period, prepared by, description, current statistics, previous-period comparison, attendance/Ibada detail, giving breakdown and AI-assisted trend interpretation.

## Database migration
Run this migration after updating an existing database:

`database/migrations/2026_09_09_001_agent_communication_settings.sql`

It ensures the communication tables/columns required by confirmed chatbot SMS/email actions and initializes the new settings keys.
