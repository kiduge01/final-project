# End-to-End Data Flow (Sample)

## Scenario: Youth Outreach Event with Procurement and Financial Tracking

1. Secretary creates an event (Youth Outreach) in Event Management.
- Event record created in events.
- Initial budget lines added in event_budget_items.
- Audit log row inserted in audit_logs.

2. Event coordinator assigns tasks.
- Task records inserted in event_tasks.
- Assigned users receive dashboard task notifications (and optional SMS reminders).

3. Coordinator submits purchase request for outreach materials.
- Record inserted in purchase_requests with status submitted.
- Approval chain rows inserted in procurement_approvals.

4. Finance Officer approves request.
- procurement_approvals.decision updated to approved.
- purchase_requests.status moves to approved.
- Audit log captures approver action.

5. Purchase Order is generated for supplier.
- purchase_orders and purchase_order_items created.
- Supplier linked through suppliers table.

6. Expense entry is auto-posted to finance.
- finance_entries inserted with source_type=procurement and purchase_order_id.
- Category mapped to PROCUREMENT expense.
- event_finance_links created so event report includes this expense.

7. Service/event day attendance is captured.
- event_attendance stores check-in records.
- Optional SMS reminders logged in sms_logs before event.

8. Event donations/offering are recorded.
- finance_entries inserted as income (donation/offering categories).
- event_finance_links stores relation_type=income.

9. Event report generated.
- Aggregates from events, event_budget_items, event_attendance, finance_entries.
- Returns attendance count, planned vs actual budget, income vs expenses, net result.

10. Dashboards and reports update.
- Dashboard reads aggregate views:
  - members_count from members where active.
  - monthly_income from finance_entries (income categories).
  - monthly_expenses from finance_entries (expense categories).
  - upcoming_events from events where start_datetime >= now.

## Audit Trail Events
- Every create/update/approve/reject/delete writes an audit_logs row.
- Captures user, module, action, entity, before/after values, IP address and user agent.

## Integration Guarantees
- Procurement expense cannot exist without finance posting reference.
- Event report always includes linked finance entries via event_finance_links.
- Asset maintenance costs can be posted to finance as maintenance expense entries.
