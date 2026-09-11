-- Events / Ibada + Attendance linkage
-- Run once on an existing installation if attendance_snapshots does not yet have event_id.

ALTER TABLE attendance_snapshots
    ADD COLUMN event_id BIGINT UNSIGNED NULL AFTER id,
    ADD INDEX idx_attendance_snapshots_event (event_id);

-- Existing historical snapshots remain valid with event_id = NULL.
-- New attendance records must select an Event / Ibada in the application.
