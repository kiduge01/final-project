# TCRIC Integrated Church Management and AI-Assisted Administration System

Final-year project for The City of Refuge International Church (TCRIC), Msasani.

## Active scope
- Members
- Guests
- Attendance
- Church Giving
- Assets
- AI-Assisted Administration
- SMS & Notifications
- Reports & Dashboard
- External/Bezaleli Integration Layer
- User Management & Security

**Department Management is explicitly out of scope.** Other out-of-scope legacy code is not routed by the application and is stored under `legacy/out_of_scope/` for reference only.

## Stack
Native PHP 8+, MySQL, HTML5, CSS3, JavaScript, Apache, XAMPP/WAMP.

## Setup
1. Create a MySQL database.
2. Import `database/schema.sql` and `database/sample_data.sql`.
3. Run `database/migrations/2026_09_01_001_final_project_scope.sql`.
4. Configure `app/config.php` or `.env` according to the existing configuration pattern.
5. Point Apache to the project `public/` directory or use the included rewrite rules.

## AI design
The AI assistant uses a controlled application-function architecture. User prompts do not become unrestricted SQL. A local/self-hosted LLM can later be connected through an AI service abstraction; the included assistant provides a safe data-grounded demonstration mode.
