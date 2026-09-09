-- Attendance session/type support for multiple sermons under one registered Event / Ibada.
-- Example: one Sunday Worship event can have First Sermon and Second Sermon attendance records.
ALTER TABLE attendance_snapshots
    ADD COLUMN session_type VARCHAR(50) NOT NULL DEFAULT 'main_service' AFTER service_type,
    ADD INDEX idx_attendance_snapshots_session_type (session_type);
