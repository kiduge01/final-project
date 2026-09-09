# Workflow Update — Events / Ibada, CRUD and AI Agent Actions

## Events / Ibada
Events / Ibada are active again as a core workflow dependency for attendance. Administrators can register, view, update and delete events. Attendance no longer asks the user to type a service/event name; the user selects a registered Event / Ibada from a dropdown. The server derives the event name, date and attendance type and stores the selected `event_id` with the attendance snapshot.

## Core CRUD
The data-entry modules now expose View / Update / Delete actions for Members, Guests, Events / Ibada, Attendance, Church Giving and Assets. Deletes remain permission-controlled and can be blocked when referential integrity would be damaged.

Sent communication history remains immutable after delivery because changing an already-sent SMS/email would falsify the audit trail. It can still be viewed in detail.

## AI Agent Navigation
The AI assistant now recognizes task requests and returns a safe action button that redirects the authorized user to the correct workflow. Supported examples include member/guest registration, event preparation, attendance recording, SMS/email composition, church-giving entry, asset registration and reports. The AI does not bypass role permissions, validation or the final human confirmation step.

## Bezaleli
No Bezaleli integration is implemented.
