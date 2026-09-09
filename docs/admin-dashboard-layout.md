# Admin Dashboard UI Layout

## Layout Structure
- Left sidebar navigation (collapsible on mobile)
- Top content header (page title + quick actions)
- KPI card row
- Main split area:
  - Recent financial transactions table
  - Upcoming events and pending tasks panel
- Secondary widgets:
  - Approval queue summary (procurement)
  - Asset maintenance alerts
  - SMS delivery status

## Mobile-First Behavior
- Sidebar becomes top compact menu
- KPI cards stack to one-column
- Tables become horizontally scrollable cards
- Primary actions remain pinned near top

## Key Dashboard Widgets
- Members Count
- Monthly Income
- Upcoming Events
- Monthly Expenses
- Pending Procurement Approvals
- Low-Condition Assets
- SMS Queue Status

## Design Tokens
- Primary color: #0e7490
- Accent color: #f59e0b
- Base background: #f6f7f4
- Text color: #0f172a
- Border radius: 0.75rem to 1rem

## UI Principles
- Keep actions visible with clear labels
- Use simple language for non-technical users
- Prioritize high-frequency tasks: member registration, attendance, cash entry
- Keep forms short and sectioned
- Show feedback for every action (saved, queued, failed)
