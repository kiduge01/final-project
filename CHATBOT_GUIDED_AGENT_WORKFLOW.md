# Church Assistant Guided Agent Workflow

The floating Church Assistant is the only AI/agent interface. Administrative tasks stay inside the chatbot.

## Interaction modes
- Quick: provide all required values in one message.
- Guided: provide one value at a time; the assistant asks for the next missing field.
- Hybrid: values already supplied are retained and only missing fields are requested.

## Supported write workflows
- Register assets
- Create Event / Ibada
- Record attendance against a registered Event / Ibada
- Register member
- Register guest
- Send SMS
- Send email

## Safety workflow
Task detection -> collect required values -> validate/resolve related records -> show complete summary -> explicit Confirm/Cancel -> execute through approved service -> audit log -> success/error response.

No write action is executed before confirmation. Pending task state expires after 10 minutes and can be cancelled by the user.
