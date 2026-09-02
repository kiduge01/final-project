<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Response;
use PDO;

final class ApiController
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Check whether a column exists on a table (migration-safety helper). */
    private function columnExists(string $table, string $column): bool
    {
        try {
            $this->pdo->query("SELECT `{$column}` FROM `{$table}` LIMIT 0");
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ───── Auth ───── */

    public function login(array $input): void
    {
        $email    = trim((string) ($input['email'] ?? ''));
        $phone    = trim((string) ($input['phone'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $identifier = $email !== '' ? $email : $phone;

        if ($identifier === '' || $password === '') {
            Response::json(['success' => false, 'message' => 'Email (or phone) and password are required'], 422);
        }

        // Brute-force check
        $check = Auth::checkLoginAllowed($this->pdo, $identifier);
        if (!$check['allowed']) {
            Response::json(['success' => false, 'message' => 'Too many login attempts. Please try again in ' . ceil($check['retry_after'] / 60) . ' minutes.'], 429);
        }

        // Look up by email first, then phone
        if ($email !== '') {
            $stmt = $this->pdo->prepare(
                'SELECT u.id, u.full_name, u.password_hash, u.role_id, r.name AS role_name
                 FROM users u INNER JOIN roles r ON r.id = u.role_id
                 WHERE u.email = :email AND u.is_active = 1 LIMIT 1'
            );
            $stmt->execute([':email' => $email]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT u.id, u.full_name, u.password_hash, u.role_id, r.name AS role_name
                 FROM users u INNER JOIN roles r ON r.id = u.role_id
                 WHERE u.phone = :phone AND u.is_active = 1 LIMIT 1'
            );
            $stmt->execute([':phone' => $phone]);
        }
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Auth::recordLoginAttempt($this->pdo, $identifier);
            Response::json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Load permissions for this role
        $user['permissions'] = $this->loadRolePermissions((int) $user['role_id']);

        Auth::login($user);
        Auth::clearLoginAttempts($this->pdo, $identifier);

        // Update last_login_at
        $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')
            ->execute([':id' => $user['id']]);

        Audit::log($this->pdo, (int) $user['id'], 'auth', 'login', 'users', (int) $user['id'], null, ['status' => 'logged_in'], 'User logged in');

        Response::json([
            'success' => true,
            'message' => 'Login successful',
            'data'    => ['user' => Auth::user()],
        ]);
    }

    /** Load permission names for a given role_id */
    private function loadRolePermissions(int $roleId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.name FROM permissions p
                 INNER JOIN role_permissions rp ON rp.permission_id = p.id
                 WHERE rp.role_id = :rid'
            );
            $stmt->execute([':rid' => $roleId]);
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ───── Forgot / Reset Password (API) ───── */

    public function forgotPassword(array $input): void
    {
        $email = trim((string) ($input['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => 'A valid email address is required'], 422);
        }

        $stmt = $this->pdo->prepare('SELECT id, full_name FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Generic response to prevent user enumeration
        if (!$user) {
            Response::json(['success' => true, 'message' => 'If an account exists with that email, a reset link has been sent.']);
        }

        $token = bin2hex(random_bytes(32));
        $hash  = password_hash($token, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = :uid')->execute([':uid' => $user['id']]);
        $this->pdo->prepare(
            'INSERT INTO password_reset_tokens (user_id, token, code_hash, expires_at, created_at)
             VALUES (:uid, :tok, :hash, :exp, NOW())'
        )->execute([':uid' => $user['id'], ':tok' => $token, ':hash' => $hash, ':exp' => $expiresAt]);

        // In production, send email with reset link. For dev, return the token.
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $resetLink = $baseUrl . '/reset-password?token=' . urlencode($token);

        Response::json([
            'success' => true,
            'message' => 'If an account exists with that email, a reset link has been sent.',
            'data'    => ['reset_link' => $resetLink], // Remove in production; send via email
        ]);
    }

    public function resetPassword(array $input): void
    {
        $token   = trim((string) ($input['token'] ?? ''));
        $newPass = (string) ($input['password'] ?? '');

        if ($token === '' || $newPass === '') {
            Response::json(['success' => false, 'message' => 'Token and new password are required'], 422);
        }
        if (mb_strlen($newPass) < 8) {
            Response::json(['success' => false, 'message' => 'Password must be at least 8 characters'], 422);
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, token, expires_at FROM password_reset_tokens WHERE token = :tok LIMIT 1'
        );
        $stmt->execute([':tok' => $token]);
        $row = $stmt->fetch();

        if (!$row || strtotime($row['expires_at']) < time()) {
            if ($row) {
                $this->pdo->prepare('DELETE FROM password_reset_tokens WHERE id = :id')->execute([':id' => $row['id']]);
            }
            Response::json(['success' => false, 'message' => 'Reset link has expired. Please request a new one.'], 410);
        }

        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $this->pdo->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :uid')
            ->execute([':hash' => $hash, ':uid' => $row['user_id']]);
        $this->pdo->prepare('DELETE FROM password_reset_tokens WHERE id = :id')->execute([':id' => $row['id']]);

        Response::json(['success' => true, 'message' => 'Password reset successfully. You can now sign in.']);
    }

    /* ───── Dashboard ───── */

    public function dashboardStats(): void
    {
        $members  = (int) $this->pdo->query("SELECT COUNT(*) FROM members WHERE member_status='active'")->fetchColumn();
        $guests = 0;
        try { $guests = (int) $this->pdo->query("SELECT COUNT(*) FROM guests")->fetchColumn(); } catch (\Throwable $e) {}
        $income   = (float) $this->pdo->query("SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe INNER JOIN finance_categories fc ON fc.id=fe.category_id WHERE fc.category_type='income' AND DATE_FORMAT(fe.entry_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn();
        $expenses = (float) $this->pdo->query("SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe INNER JOIN finance_categories fc ON fc.id=fe.category_id WHERE fc.category_type='expense' AND DATE_FORMAT(fe.entry_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn();
        $attendance = 0;
        try { $attendance = (int) $this->pdo->query("SELECT COALESCE(SUM(total_count),0) FROM attendance_snapshots WHERE DATE_FORMAT(service_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn(); } catch (\Throwable $e) {}
        Response::json([
            'success' => true,
            'message' => 'Dashboard stats',
            'data' => compact('members', 'guests', 'attendance', 'income', 'expenses'),
        ]);
    }

    public function dashboardInsights(): void
    {
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            Response::json(['success' => false, 'message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $eventStmt = $this->pdo->prepare(
            'SELECT e.id, e.title, e.category, e.start_datetime, e.venue, e.target_group_id, e.notes
             FROM `events` e
             WHERE e.start_datetime BETWEEN :start_at AND :end_at
             ORDER BY e.start_datetime ASC'
        );
        $eventStmt->execute([
            ':start_at' => $monthStart . ' 00:00:00',
            ':end_at' => $monthEnd . ' 23:59:59',
        ]);

        $dbEvents = array_map(function (array $event): array {
            $event['kind'] = $this->resolveEventKind((string) $event['category'], (string) ($event['notes'] ?? ''));
            $event['is_system'] = false;
            $event['is_editable'] = true;
            $event['tag'] = $this->kindTag((string) $event['kind']);
            return $event;
        }, $eventStmt->fetchAll());

        $systemEvents = $this->buildSystemChurchEvents($month);
        $allMonthEvents = array_merge($dbEvents, $systemEvents);
        usort($allMonthEvents, fn (array $a, array $b): int => strcmp((string) $a['start_datetime'], (string) $b['start_datetime']));

        $now = date('Y-m-d H:i:s');
        $upcoming = array_values(array_filter($allMonthEvents, fn (array $e): bool => (string) $e['start_datetime'] >= $now));
        $upcoming = array_slice($upcoming, 0, 5);

        $this->ensureAttendanceSnapshotsTable();

        $sundaySummaries = [];
        foreach ($this->sundaysInMonth($monthStart) as $sundayDate) {
            // Primary source: aggregate attendance snapshots from Attendance Center.
            $snapshotAttendanceStmt = $this->pdo->prepare(
                'SELECT COALESCE(SUM(total_count), 0)
                 FROM attendance_snapshots
                 WHERE service_date = :sunday'
            );
            $snapshotAttendanceStmt->execute([':sunday' => $sundayDate]);
            $attendance = (int) $snapshotAttendanceStmt->fetchColumn();

            // Backward compatibility: fallback to event-level attendance data when snapshots are missing.
            if ($attendance === 0) {
                $attendanceStmt = $this->pdo->prepare(
                    "SELECT COALESCE(SUM(CASE WHEN ea.status='present' THEN 1 ELSE 0 END), 0)
                     FROM event_attendance ea
                     INNER JOIN events e ON e.id = ea.event_id
                     WHERE DATE(e.start_datetime) = :sunday"
                );
                $attendanceStmt->execute([':sunday' => $sundayDate]);
                $attendance = (int) $attendanceStmt->fetchColumn();
            }

            $offeringStmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(fe.amount), 0)
                 FROM finance_entries fe
                 INNER JOIN finance_categories fc ON fc.id = fe.category_id
                 WHERE fc.category_type = 'income' AND DATE(fe.entry_date) = :sunday"
            );
            $offeringStmt->execute([':sunday' => $sundayDate]);
            $offering = (float) $offeringStmt->fetchColumn();

            $sundaySummaries[$sundayDate] = [
                'attendance' => $attendance,
                'offering' => $offering,
            ];
        }

        $lastSundayDate = date('Y-m-d', strtotime('-' . date('w') . ' days'));
        $lastSundaySummary = $sundaySummaries[$lastSundayDate] ?? ['attendance' => 0, 'offering' => 0.0];

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weeklyStmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(fe.amount), 0)
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id = fe.category_id
             WHERE fc.category_type = 'income' AND DATE(fe.entry_date) BETWEEN :week_start AND CURRENT_DATE"
        );
        $weeklyStmt->execute([':week_start' => $weekStart]);
        $weeklyOffering = (float) $weeklyStmt->fetchColumn();

        $monthlyStmt = $this->pdo->query(
            "SELECT COALESCE(SUM(fe.amount), 0)
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id = fe.category_id
             WHERE fc.category_type = 'income' AND DATE_FORMAT(fe.entry_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')"
        );
        $monthlyIncome = (float) $monthlyStmt->fetchColumn();

        $nextSundayDate = date('Y-m-d', strtotime('next sunday'));
        $nextSundayFocus = null;
        foreach ($allMonthEvents as $event) {
            if (str_starts_with((string) $event['start_datetime'], $nextSundayDate)) {
                $nextSundayFocus = $event;
                break;
            }
        }
        if ($nextSundayFocus === null) {
            $nextSundayFocus = [
                'title' => 'Worship Service',
                'start_datetime' => $nextSundayDate . ' 09:00:00',
                'tag' => 'Worship',
                'is_system' => true,
            ];
        }

        $specialUpcoming = null;
        foreach ($allMonthEvents as $event) {
            if (($event['kind'] ?? '') === 'special' && (string) $event['start_datetime'] >= $now) {
                $specialUpcoming = $event;
                break;
            }
        }

        Response::json([
            'success' => true,
            'message' => 'Dashboard insights',
            'data' => [
                'upcoming' => $upcoming,
                'sunday_summaries' => $sundaySummaries,
                'last_sunday' => [
                    'date' => $lastSundayDate,
                    'attendance' => (int) $lastSundaySummary['attendance'],
                    'offering' => (float) $lastSundaySummary['offering'],
                    'trend' => $weeklyOffering >= (float) $lastSundaySummary['offering'] ? 'up' : 'down',
                ],
                'highlights' => [
                    'next_sunday_focus' => $nextSundayFocus,
                    'special_upcoming' => $specialUpcoming,
                ],
                'financial_snapshot' => [
                    'last_sunday_offering' => (float) $lastSundaySummary['offering'],
                    'weekly_total' => $weeklyOffering,
                    'monthly_income' => $monthlyIncome,
                ],
            ],
        ]);
    }

    /* ───── Members ───── */

    public function listMembers(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $gender = trim((string) ($_GET['gender'] ?? ''));

        $sql = 'SELECT m.id, m.member_code, m.first_name, m.last_name, m.phone, m.email, m.gender,
                       m.member_status, m.join_date, m.ward, m.district, m.region, m.date_of_birth
                FROM members m
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (m.first_name LIKE :s1 OR m.last_name LIKE :s2 OR m.phone LIKE :s3 OR m.member_code LIKE :s4 OR m.email LIKE :s5)';
            $like = '%' . $search . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
            $params[':s4'] = $like;
            $params[':s5'] = $like;
        }
        if ($status !== '' && in_array($status, ['active', 'inactive', 'transferred', 'deceased'], true)) {
            $sql .= ' AND m.member_status = :status';
            $params[':status'] = $status;
        }
        if ($gender !== '' && in_array($gender, ['male', 'female', 'other'], true)) {
            $sql .= ' AND m.gender = :gender';
            $params[':gender'] = $gender;
        }

        $sql .= ' ORDER BY m.id DESC LIMIT 500';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        Response::json(['success' => true, 'message' => 'Members list', 'data' => $stmt->fetchAll()]);
    }

    public function memberStats(): void
    {
        $row = $this->pdo->query(
            "SELECT
                COUNT(*) AS total,
                SUM(member_status = 'active') AS active,
                SUM(member_status = 'inactive') AS inactive,
                SUM(member_status = 'transferred') AS transferred,
                SUM(member_status = 'deceased') AS deceased,
                SUM(gender = 'male') AS male,
                SUM(gender = 'female') AS female
             FROM members"
        )->fetch();
        Response::json(['success' => true, 'message' => 'Member stats', 'data' => $row]);
    }

    public function createMember(array $input): void
    {
        if (!Auth::can('members.create')) {
            Response::json(['success' => false, 'message' => 'No permission to create members'], 403); return;
        }
        $required = ['first_name', 'last_name', 'gender', 'phone'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $user    = Auth::user();
        $actorId = $user['id'] ?? null;

        // Auto-generate member code if not provided
        $memberCode = trim((string) ($input['member_code'] ?? ''));
        if ($memberCode === '') {
            $codeStmt = $this->pdo->query(
                "SELECT CONCAT('MBR-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(COALESCE(MAX(id), 0) + 1, 4, '0')) FROM members"
            );
            $memberCode = (string) $codeStmt->fetchColumn();
        }

        $joinDate = trim((string) ($input['join_date'] ?? ''));
        if ($joinDate === '' || strtotime($joinDate) === false) {
            $joinDate = date('Y-m-d');
        }

        $location = trim((string) (($input['physical_address'] ?? $input['location'] ?? '')));
        $status = in_array($input['member_status'] ?? '', ['active', 'inactive', 'transferred', 'deceased'], true)
            ? $input['member_status']
            : 'active';

        $stmt = $this->pdo->prepare(
            'INSERT INTO members (member_code, first_name, last_name, gender, phone, email,
                                  join_date, marital_status, physical_address, member_status,
                                  created_by, updated_by)
             VALUES (:member_code, :first_name, :last_name, :gender, :phone, :email,
                     :join_date, :marital_status, :physical_address, :status,
                     :created_by, :updated_by)'
        );

        $n = fn(string $k): ?string => (isset($input[$k]) && $input[$k] !== '') ? trim((string) $input[$k]) : null;

        $stmt->execute([
            ':member_code'      => $memberCode,
            ':first_name'       => trim((string) $input['first_name']),
            ':last_name'        => trim((string) $input['last_name']),
            ':gender'           => trim((string) $input['gender']),
            ':phone'            => trim((string) $input['phone']),
            ':email'            => $n('email'),
            ':join_date'        => $joinDate,
            ':marital_status'   => $n('marital_status'),
            ':physical_address' => $location !== '' ? $location : null,
            ':status'           => $status,
            ':created_by'       => $actorId,
            ':updated_by'       => $actorId,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        
        Audit::log($this->pdo, $actorId ? (int) $actorId : null, 'members', 'create', 'members', $id, null, ['member_code' => $memberCode, 'name' => $input['first_name'] . ' ' . $input['last_name']], 'Created member profile');

        Response::json(['success' => true, 'message' => 'Member created', 'data' => ['id' => $id, 'member_code' => $memberCode]], 201);
    }

    public function updateMember(int $id, array $input): void
    {
        if (!Auth::can('members.edit')) {
            Response::json(['success' => false, 'message' => 'No permission to edit members'], 403); return;
        }
        $allowed = ['first_name','last_name','gender','phone','email','member_code','join_date','marital_status','physical_address','member_status'];
        $set = [];
        $params = [':id' => $id];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                $params[":$field"] = ($value !== '' && $value !== null) ? $value : null;
                $set[] = "`$field` = :$field";
            }
        }
        if (empty($set)) {
            Response::json(['success' => false, 'message' => 'Nothing to update'], 422);
        }

        $user = Auth::user();
        $actorId = $user['id'] ?? null;
        $set[] = 'updated_by = :updated_by';
        $params[':updated_by'] = $actorId;

        $stmt = $this->pdo->prepare('UPDATE members SET ' . implode(', ', $set) . ' WHERE id = :id');
        $stmt->execute($params);

        Audit::log($this->pdo, $actorId ? (int) $actorId : null, 'members', 'update', 'members', $id, null, $input, 'Updated member profile');
        Response::json(['success' => true, 'message' => 'Member updated']);
    }

    public function importMembers(): void
    {
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'No valid file uploaded. Please select a CSV or Excel file.'], 422);
        }

        $file = $_FILES['file'];
        $ext  = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'xlsx'], true)) {
            Response::json(['success' => false, 'message' => 'Only .csv and .xlsx files are supported.'], 422);
        }

        try {
            $rows = $ext === 'csv' ? $this->parseCsv((string) $file['tmp_name']) : $this->parseXlsx((string) $file['tmp_name']);
        } catch (\RuntimeException $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        if (count($rows) < 2) {
            Response::json(['success' => false, 'message' => 'File has no data rows (first row must be header).'], 422);
        }

        $header   = array_map(fn($v) => strtolower(trim((string) $v)), $rows[0]);
        $dataRows = array_slice($rows, 1);

        // Flexible column alias map
        $aliases = [
            'first_name'       => ['first_name','firstname','first name','jina la kwanza','jina'],
            'last_name'        => ['last_name','lastname','last name','surname','familia','jina la familia'],
            'gender'           => ['gender','jinsia','sex'],
            'phone'            => ['phone','simu','mobile','phone_number','phone number','nambari ya simu'],
            'email'            => ['email','barua pepe','barua_pepe'],
            'date_of_birth'    => ['date_of_birth','dob','birthdate','birth date','birth_date','tarehe ya kuzaliwa'],
            'join_date'        => ['join_date','joined','join date','date joined','tarehe ya kujiunga'],
            'member_status'    => ['member_status','status','hali'],
            'member_code'      => ['member_code','code','member code','nambari','namba'],
            'physical_address' => ['physical_address','address','makazi','anuani'],
            'ward'             => ['ward','mtaa'],
            'district'         => ['district','wilaya'],
            'region'           => ['region','mkoa'],
            'marital_status'   => ['marital_status','marital status','hali ya ndoa'],
            'baptism_date'     => ['baptism_date','baptism date','tarehe ya ubatizo'],
            'notes'            => ['notes','maelezo','note'],
        ];

        $fieldIdx = [];
        foreach ($aliases as $field => $aliasList) {
            foreach ($aliasList as $alias) {
                $pos = array_search($alias, $header, true);
                if ($pos !== false) {
                    $fieldIdx[$field] = (int) $pos;
                    break;
                }
            }
        }

        $user    = Auth::user();
        $actorId = $user['id'] ?? null;
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        $stmt = $this->pdo->prepare(
            'INSERT INTO members (member_code, first_name, last_name, gender, phone, email,
                                  date_of_birth, join_date, member_status, physical_address, ward,
                                  district, region, marital_status, baptism_date, notes,
                                  created_by, updated_by)
             VALUES (:member_code, :first_name, :last_name, :gender, :phone, :email,
                     :date_of_birth, :join_date, :member_status, :physical_address, :ward,
                     :district, :region, :marital_status, :baptism_date, :notes,
                     :created_by, :updated_by)
             ON DUPLICATE KEY UPDATE updated_at = updated_at'
        );

        foreach ($dataRows as $i => $row) {
            $rowNum = $i + 2;
            $get    = fn(string $f): string => isset($fieldIdx[$f]) ? trim((string) ($row[$fieldIdx[$f]] ?? '')) : '';
            $nul    = fn(string $f): ?string => ($v = $get($f)) !== '' ? $v : null;

            $firstName = $get('first_name');
            $lastName  = $get('last_name');
            if ($firstName === '' || $lastName === '') {
                $errors[] = "Row $rowNum: first_name and last_name are required — skipped.";
                $skipped++;
                continue;
            }

            $gender = strtolower($get('gender'));
            if (!in_array($gender, ['male', 'female', 'other'], true)) {
                $gender = 'other';
            }

            $memberCode = $get('member_code');
            if ($memberCode === '') {
                $cs = $this->pdo->query("SELECT CONCAT('MBR-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(COALESCE(MAX(id),0)+1,4,'0')) FROM members");
                $memberCode = (string) $cs->fetchColumn();
            }

            $joinDate = $get('join_date');
            $joinDate = ($joinDate !== '' && strtotime($joinDate) !== false) ? date('Y-m-d', strtotime($joinDate)) : date('Y-m-d');

            $dob  = ($v = $get('date_of_birth'))  !== '' && strtotime($v) ? date('Y-m-d', strtotime($v)) : null;
            $bapt = ($v = $get('baptism_date'))    !== '' && strtotime($v) ? date('Y-m-d', strtotime($v)) : null;

            $status   = $get('member_status');
            $status   = in_array($status, ['active','inactive','transferred','deceased'], true) ? $status : 'active';
            $marital  = $get('marital_status');
            $marital  = in_array($marital, ['single','married','widowed','divorced'], true) ? $marital : null;
            
            try {
                $stmt->execute([
                    ':member_code'     => $memberCode,
                    ':first_name'      => $firstName,
                    ':last_name'       => $lastName,
                    ':gender'          => $gender,
                    ':phone'           => $get('phone'),
                    ':email'           => $nul('email'),
                    ':date_of_birth'   => $dob,
                    ':join_date'       => $joinDate,
                    ':member_status'   => $status,
                    ':physical_address'=> $nul('physical_address'),
                    ':ward'            => $nul('ward'),
                    ':district'        => $nul('district'),
                    ':region'          => $nul('region'),
                    ':marital_status'  => $marital,
                    ':baptism_date'    => $bapt,
                    ':notes'           => $nul('notes'),
                    ':created_by'      => $actorId,
                    ':updated_by'      => $actorId,
                ]);
                $inserted++;
            } catch (\PDOException $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    $skipped++;
                } else {
                    $errors[] = "Row $rowNum: " . $e->getMessage();
                    $skipped++;
                }
            }
        }

        Audit::log($this->pdo, $actorId ? (int) $actorId : null, 'members', 'import', 'members', null, null, ['inserted' => $inserted, 'skipped' => $skipped], "Imported $inserted members from $ext file");

        Response::json([
            'success' => true,
            'message' => "Import complete: $inserted inserted, $skipped skipped.",
            'data'    => ['inserted' => $inserted, 'skipped' => $skipped, 'errors' => array_slice($errors, 0, 20)],
        ]);
    }

    private function parseCsv(string $filePath): array
    {
        $rows   = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open CSV file.');
        }
        while (($row = fgetcsv($handle)) !== false) {
            if (!empty(array_filter($row, fn($v) => trim((string) $v) !== ''))) {
                $rows[] = $row;
            }
        }
        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive PHP extension is required to parse .xlsx files. Please use CSV instead.');
        }
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Cannot open .xlsx file. The file may be corrupted.');
        }

        // Shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            libxml_use_internal_errors(true);
            $ss = simplexml_load_string($ssXml);
            if ($ss) {
                foreach ($ss->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        // Find first worksheet path
        $wsName = 'xl/worksheets/sheet1.xml';
        $wsXml  = $zip->getFromName($wsName);

        // Try workbook rels if sheet1 not found directly
        if ($wsXml === false) {
            $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
            if ($relsXml !== false) {
                $rels = simplexml_load_string($relsXml);
                if ($rels) {
                    foreach ($rels->Relationship as $rel) {
                        $t = (string) $rel['Type'];
                        if (str_contains($t, 'worksheet')) {
                            $wsName = 'xl/' . ltrim((string) $rel['Target'], '/');
                            $wsXml  = $zip->getFromName($wsName);
                            break;
                        }
                    }
                }
            }
        }
        $zip->close();

        if ($wsXml === false) {
            throw new \RuntimeException('Cannot read worksheet data from .xlsx file.');
        }

        libxml_use_internal_errors(true);
        $ws = simplexml_load_string($wsXml);
        if (!$ws) {
            throw new \RuntimeException('Worksheet XML is invalid.');
        }

        $rows = [];
        foreach ($ws->sheetData->row as $row) {
            $cells  = [];
            $maxCol = 0;
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                preg_match('/^([A-Z]+)/i', $ref, $m);
                $letters  = strtoupper($m[1] ?? 'A');
                $colIndex = 0;
                for ($ci = 0; $ci < strlen($letters); $ci++) {
                    $colIndex = $colIndex * 26 + (ord($letters[$ci]) - 64);
                }
                $colIndex--;
                $type  = (string) ($cell['t'] ?? '');
                $value = isset($cell->v) ? (string) $cell->v : '';
                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($cell->is->t) ? (string) $cell->is->t : '';
                }
                $cells[$colIndex] = $value;
                if ($colIndex > $maxCol) {
                    $maxCol = $colIndex;
                }
            }
            $rowArr = [];
            for ($ci = 0; $ci <= $maxCol; $ci++) {
                $rowArr[] = $cells[$ci] ?? '';
            }
            if (!empty(array_filter($rowArr, fn($v) => trim((string) $v) !== ''))) {
                $rows[] = $rowArr;
            }
        }
        return $rows;
    }

    /* ───── Events ───── */

    public function listEvents(): void
    {
        $month = trim((string) ($_GET['month'] ?? ''));
        $type  = trim((string) ($_GET['type'] ?? ''));
        $group = trim((string) ($_GET['group'] ?? ''));

        $hasBudgetCols = $this->columnExists('events', 'budget_status');

        if ($hasBudgetCols) {
            $budgetSelect = 'e.budget_status, e.budget_approved_by, e.budget_approved_at,
                       ba.full_name AS budget_approver_name';
            $budgetJoin   = 'LEFT JOIN users ba ON ba.id = e.budget_approved_by';
        } else {
            // Derive budget_status from finance_entries when migration column not yet added
            $hasApprovalStatus = $this->columnExists('finance_entries', 'approval_status');
            if ($hasApprovalStatus) {
                $budgetSelect = "(SELECT CASE fe2.approval_status
                                     WHEN 'pending'  THEN 'pending_approval'
                                     WHEN 'approved' THEN 'approved'
                                     WHEN 'rejected' THEN 'rejected'
                                     ELSE 'pending_approval'
                                 END
                                 FROM finance_entries fe2
                                 WHERE fe2.source_type='event' AND fe2.event_id=e.id
                                 ORDER BY fe2.id DESC LIMIT 1) AS budget_status,
                       NULL AS budget_approved_by, NULL AS budget_approved_at,
                       NULL AS budget_approver_name";
            } else {
                $budgetSelect = "CASE
                                   WHEN EXISTS(SELECT 1 FROM finance_entries fe2 WHERE fe2.source_type='event' AND fe2.event_id=e.id AND fe2.approved_by IS NOT NULL) THEN 'approved'
                                   WHEN EXISTS(SELECT 1 FROM finance_entries fe2 WHERE fe2.source_type='event' AND fe2.event_id=e.id) THEN 'pending_approval'
                                   ELSE 'draft'
                                 END AS budget_status,
                       NULL AS budget_approved_by, NULL AS budget_approved_at,
                       NULL AS budget_approver_name";
            }
            $budgetJoin = '';
        }

        $sql = "SELECT e.id, e.event_code, e.title, e.description, e.event_type, e.start_datetime, e.end_datetime,
                       e.location, e.pastor_on_duty, e.usher_on_duty, e.status, e.budget_total,
                       {$budgetSelect}
                FROM `events` e
                {$budgetJoin}
                WHERE 1=1";

        $params = [];

        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $sql .= ' AND DATE_FORMAT(e.start_datetime, "%Y-%m") = :month';
            $params[':month'] = $month;
        }

        if ($type !== '') {
            $sql .= ' AND e.event_type = :event_type';
            $params[':event_type'] = $type;
        }

        $sql .= ' ORDER BY e.start_datetime ASC LIMIT 400';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        Response::json(['success' => true, 'message' => 'Events list', 'data' => $rows]);
    }

    public function calendarEvents(): void
    {
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            Response::json(['success' => false, 'message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        $monthStart = $month . '-01 00:00:00';
        $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));

        $stmt = $this->pdo->prepare(
            'SELECT e.id, e.event_code, e.title, e.event_type, e.start_datetime, e.end_datetime, e.status,
                e.location, e.pastor_on_duty, e.usher_on_duty, e.budget_total
             FROM `events` e
             WHERE e.start_datetime BETWEEN :start_at AND :end_at
             ORDER BY e.start_datetime ASC'
        );
        $stmt->execute([
            ':start_at' => $monthStart,
            ':end_at' => $monthEnd,
        ]);

        $rows = $stmt->fetchAll();
        $normalized = array_map(function (array $event): array {
            $kind = $this->resolveEventKind((string) $event['category'], (string) ($event['notes'] ?? ''));
            $event['kind'] = $kind;
            $event['is_system'] = false;
            $event['is_editable'] = true;
            $event['tag'] = $this->kindTag($kind);
            return $event;
        }, $rows);

        $calendarEvents = array_merge($normalized, $this->buildSystemChurchEvents($month));
        usort($calendarEvents, fn (array $a, array $b): int => strcmp((string) $a['start_datetime'], (string) $b['start_datetime']));

        Response::json([
            'success' => true,
            'message' => 'Calendar events',
            'data' => [
                'month' => $month,
                'events' => $calendarEvents,
            ],
        ]);
    }

    public function createEvent(array $input): void
    {
        if (!Auth::can('events.create')) {
            Response::json(['success' => false, 'message' => 'No permission to create events'], 403); return;
        }
        $required = ['title', 'event_type', 'date', 'time', 'location'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $eventType = trim((string) $input['event_type']);
        $categoryMap = [
            'service' => 'conference',
            'seminar' => 'seminar',
            'meeting' => 'other',
            'appointment' => 'other',
        ];
        $category = $categoryMap[$eventType] ?? 'other';

        $date = trim((string) $input['date']);
        $time = trim((string) $input['time']);
        $startDatetime = date('Y-m-d H:i:s', strtotime($date . ' ' . $time));
        if ($startDatetime === false || $startDatetime === '1970-01-01 00:00:00') {
            Response::json(['success' => false, 'message' => 'Invalid date/time value'], 422);
        }

        $durationHours = 2.0;
        $endDatetime = date('Y-m-d H:i:s', strtotime($startDatetime . ' +' . $durationHours . ' hour'));

        $codeStmt = $this->pdo->query("SELECT CONCAT('EVT-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(COALESCE(MAX(id), 0) + 1, 3, '0')) FROM `events`");
        $eventCode = (string) $codeStmt->fetchColumn();

        $budget = isset($input['budget']) && $input['budget'] !== '' ? (float) $input['budget'] : 0.0;
        $sendSms = !empty($input['send_sms']);
        $sendEmail = !empty($input['send_email']);

        $pastorOnDuty = trim((string) ($input['pastor_on_duty'] ?? '')) ?: null;
        $usherOnDuty = trim((string) ($input['usher_on_duty'] ?? '')) ?: null;
        $location = trim((string) ($input['location'] ?? '')) ?: null;
        $description = trim((string) ($input['description'] ?? '')) ?: null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO `events` (event_code, title, description, event_type, start_datetime, end_datetime, 
                                   location, pastor_on_duty, usher_on_duty, status, budget_total)
             VALUES (:event_code, :title, :description, :event_type, :start_datetime, :end_datetime, 
                     :location, :pastor_on_duty, :usher_on_duty, :status, :budget_total)'
        );
        $stmt->execute([
            ':event_code' => $eventCode,
            ':title' => trim((string) $input['title']),
            ':description' => $description,
            ':event_type' => $eventType,
            ':start_datetime' => $startDatetime,
            ':end_datetime' => $endDatetime,
            ':location' => $location,
            ':pastor_on_duty' => $pastorOnDuty,
            ':usher_on_duty' => $usherOnDuty,
            ':status' => 'planned',
            ':budget_total' => $budget,
        ]);

        $eventId = (int) $this->pdo->lastInsertId();
        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;

        if ($budget > 0) {
            $budgetStmt = $this->pdo->prepare(
                'INSERT INTO event_budget_items (event_id, item_type, item_name, planned_amount, actual_amount, notes)
                 VALUES (:event_id, :item_type, :item_name, :planned_amount, :actual_amount, :notes)'
            );
            $budgetStmt->execute([
                ':event_id' => $eventId,
                ':item_type' => 'expense',
                ':item_name' => 'Event Budget',
                ':planned_amount' => $budget,
                ':actual_amount' => 0,
                ':notes' => 'Budget for ' . trim((string) $input['title']),
            ]);
        }

        Audit::log($this->pdo, $actorId, 'events', 'create', 'events', $eventId, null, $input, 'Created event');

        Response::json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => [
                'id' => $eventId,
                'event_code' => $eventCode,
            ],
        ], 201);
    }

    /** Delete an event */
    public function deleteEvent(int $eventId): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('events.delete')) {
            Response::json(['success' => false, 'message' => 'No permission to delete events'], 403); return;
        }

        $eventStmt = $this->pdo->prepare('SELECT id FROM `events` WHERE id = :id LIMIT 1');
        $eventStmt->execute([':id' => $eventId]);
        $event = $eventStmt->fetch();
        if (!$event) {
            Response::json(['success' => false, 'message' => 'Event not found'], 404);
            return;
        }

        $this->pdo->beginTransaction();
        try {
            // Delete related records
            $this->pdo->prepare('DELETE FROM event_attendance WHERE event_id = :id')->execute([':id' => $eventId]);
            $this->pdo->prepare('DELETE FROM event_budget_items WHERE event_id = :id')->execute([':id' => $eventId]);
            $this->pdo->prepare('DELETE FROM event_tasks WHERE event_id = :id')->execute([':id' => $eventId]);
            $this->pdo->prepare('DELETE FROM event_finance_links WHERE event_id = :id')->execute([':id' => $eventId]);
            $this->pdo->prepare('DELETE FROM sms_logs WHERE event_id = :id')->execute([':id' => $eventId]);
            
            // Delete the event
            $this->pdo->prepare('DELETE FROM `events` WHERE id = :id')->execute([':id' => $eventId]);

            Audit::log($this->pdo, (int) $user['id'], 'events', 'delete', 'events', $eventId, null, null, "Event deleted");

            $this->pdo->commit();
            Response::json(['success' => true, 'message' => 'Event deleted successfully']);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed to delete event: ' . $e->getMessage()], 500);
        }
    }

    public function eventDetails(int $eventId): void
    {
        $hasBudgetCols = $this->columnExists('events', 'budget_status');

        if ($hasBudgetCols) {
            $budgetSelect = 'e.budget_status, e.budget_approved_by, e.budget_approved_at,
                    ba.full_name AS budget_approver_name';
            $budgetJoin   = 'LEFT JOIN users ba ON ba.id = e.budget_approved_by';
        } else {
            // Derive budget_status from finance_entries when migration column not yet added
            $hasApprovalStatus = $this->columnExists('finance_entries', 'approval_status');
            if ($hasApprovalStatus) {
                $budgetSelect = "(SELECT CASE fe2.approval_status
                                     WHEN 'pending'  THEN 'pending_approval'
                                     WHEN 'approved' THEN 'approved'
                                     WHEN 'rejected' THEN 'rejected'
                                     ELSE 'pending_approval'
                                 END
                                 FROM finance_entries fe2
                                 WHERE fe2.source_type='event' AND fe2.event_id=e.id
                                 ORDER BY fe2.id DESC LIMIT 1) AS budget_status,
                        NULL AS budget_approved_by, NULL AS budget_approved_at,
                        (SELECT u2.full_name FROM finance_entries fe2 INNER JOIN users u2 ON u2.id=fe2.approved_by WHERE fe2.source_type='event' AND fe2.event_id=e.id AND fe2.approved_by IS NOT NULL ORDER BY fe2.id DESC LIMIT 1) AS budget_approver_name";
            } else {
                $budgetSelect = "CASE
                                   WHEN EXISTS(SELECT 1 FROM finance_entries fe2 WHERE fe2.source_type='event' AND fe2.event_id=e.id AND fe2.approved_by IS NOT NULL) THEN 'approved'
                                   WHEN EXISTS(SELECT 1 FROM finance_entries fe2 WHERE fe2.source_type='event' AND fe2.event_id=e.id) THEN 'pending_approval'
                                   ELSE 'draft'
                                 END AS budget_status,
                        NULL AS budget_approved_by, NULL AS budget_approved_at, NULL AS budget_approver_name";
            }
            $budgetJoin = '';
        }

        $eventStmt = $this->pdo->prepare(
            "SELECT e.id, e.event_code, e.title, e.description, e.event_type, e.start_datetime, e.end_datetime,
                    e.location, e.pastor_on_duty, e.usher_on_duty, e.status, e.budget_total,
                    {$budgetSelect}
             FROM `events` e
             {$budgetJoin}
             WHERE e.id = :id LIMIT 1"
        );
        $eventStmt->execute([':id' => $eventId]);
        $event = $eventStmt->fetch();
        if (!$event) {
            Response::json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $budgetStmt = $this->pdo->prepare(
            'SELECT id, item_type, item_name, planned_amount, actual_amount, notes
             FROM event_budget_items
             WHERE event_id = :id
             ORDER BY id ASC'
        );
        $budgetStmt->execute([':id' => $eventId]);
        $budgetItems = $budgetStmt->fetchAll();

        $plannedBudget = (float) $event['budget_total'];
        $actualExpenses = 0.0;
        foreach ($budgetItems as $item) {
            if (($item['item_type'] ?? '') === 'expense') {
                $actualExpenses += (float) ($item['actual_amount'] ?? 0);
            }
        }

        $taskStmt = $this->pdo->prepare(
            'SELECT et.id, et.title, et.details, et.due_datetime, et.task_status, et.priority, u.full_name AS assigned_to
             FROM event_tasks et
             INNER JOIN users u ON u.id = et.assigned_to_user_id
             WHERE et.event_id = :id
             ORDER BY et.due_datetime IS NULL, et.due_datetime ASC, et.id ASC'
        );
        $taskStmt->execute([':id' => $eventId]);
        $tasks = $taskStmt->fetchAll();

        $attendanceTotalsStmt = $this->pdo->prepare(
            "SELECT
                SUM(CASE WHEN status='registered' THEN 1 ELSE 0 END) AS registered_count,
                SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) AS absent_count
             FROM event_attendance
             WHERE event_id = :id"
        );
        $attendanceTotalsStmt->execute([':id' => $eventId]);
        $attendanceTotals = $attendanceTotalsStmt->fetch() ?: [];

        $attendanceListStmt = $this->pdo->prepare(
            'SELECT ea.id, ea.member_id, ea.status, ea.check_in_datetime, m.member_code,
                    CONCAT(m.first_name, " ", m.last_name) AS member_name, m.phone
             FROM event_attendance ea
             INNER JOIN members m ON m.id = ea.member_id
             WHERE ea.event_id = :id
             ORDER BY member_name ASC'
        );
        $attendanceListStmt->execute([':id' => $eventId]);
        $attendanceMembers = $attendanceListStmt->fetchAll();

        $commStmt = $this->pdo->prepare(
            'SELECT id, phone, message_text, message_type, delivery_status, sent_at
             FROM sms_logs
             WHERE event_id = :id
             ORDER BY sent_at DESC, id DESC
             LIMIT 20'
        );
        $commStmt->execute([':id' => $eventId]);
        $communications = $commStmt->fetchAll();

        $financeStmt = $this->pdo->prepare(
            "SELECT
                SUM(CASE WHEN efl.relation_type='income' THEN fe.amount ELSE 0 END) AS income_total,
                SUM(CASE WHEN efl.relation_type='expense' THEN fe.amount ELSE 0 END) AS expense_total
             FROM event_finance_links efl
             INNER JOIN finance_entries fe ON fe.id = efl.finance_entry_id
             WHERE efl.event_id = :id"
        );
        $financeStmt->execute([':id' => $eventId]);
        $financials = $financeStmt->fetch() ?: [];
        $incomeTotal  = (float) ($financials['income_total'] ?? 0);
        $expenseTotal = (float) ($financials['expense_total'] ?? 0);

        $report = [
            'income_total' => $incomeTotal,
            'expense_total' => $expenseTotal,
            'net_total' => $incomeTotal - $expenseTotal,
            'final_summary' => $incomeTotal - $expenseTotal >= 0
                ? 'Event ended with positive balance.'
                : 'Event ended with budget overrun.',
        ];

        Response::json([
            'success' => true,
            'message' => 'Event details',
            'data' => [
                'overview' => $event,
                'budget' => [
                    'planned_budget' => $plannedBudget,
                    'actual_expenses' => $actualExpenses,
                    'remaining_balance' => $plannedBudget - $actualExpenses,
                    'items' => $budgetItems,
                    'status' => $event['budget_status'] ?? 'draft',
                    'approved_by' => $event['budget_approver_name'] ?? null,
                    'approved_at' => $event['budget_approved_at'] ?? null,
                    'locked' => in_array($event['budget_status'] ?? 'draft', ['pending_approval', 'approved']),
                ],
                'tasks' => $tasks,
                'attendance' => [
                    'registered_count' => (int) ($attendanceTotals['registered_count'] ?? 0),
                    'present_count' => (int) ($attendanceTotals['present_count'] ?? 0),
                    'absent_count' => (int) ($attendanceTotals['absent_count'] ?? 0),
                    'members' => $attendanceMembers,
                ],
                'communication' => [
                    'sms_logs' => $communications,
                    'email_note' => 'Email queue is simulated in this version.',
                ],
                'report' => $report,
            ],
        ]);
    }

    public function sendEventCommunication(int $eventId, array $input): void
    {
        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        if ($actorId === null) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $message = trim((string) ($input['message'] ?? ''));
        $sendSms = !empty($input['send_sms']);
        $sendEmail = !empty($input['send_email']);

        if ($message === '') {
            Response::json(['success' => false, 'message' => 'Message is required'], 422);
        }
        if (!$sendSms && !$sendEmail) {
            Response::json(['success' => false, 'message' => 'Select at least one channel (SMS or Email)'], 422);
        }

        $recipientsStmt = $this->pdo->prepare(
            'SELECT m.id, m.phone, m.email
             FROM event_attendance ea
             INNER JOIN members m ON m.id = ea.member_id
             WHERE ea.event_id = :id'
        );
        $recipientsStmt->execute([':id' => $eventId]);
        $recipients = $recipientsStmt->fetchAll();

        $smsCount = 0;
        if ($sendSms) {
            $smsStmt = $this->pdo->prepare(
                'INSERT INTO sms_logs (recipient_type, member_id, phone, message_text, message_type, provider, delivery_status, event_id, sent_by, sent_at)
                 VALUES (:recipient_type, :member_id, :phone, :message_text, :message_type, :provider, :delivery_status, :event_id, :sent_by, :sent_at)'
            );

            foreach ($recipients as $recipient) {
                if (empty($recipient['phone'])) {
                    continue;
                }
                $smsStmt->execute([
                    ':recipient_type' => 'member',
                    ':member_id' => (int) $recipient['id'],
                    ':phone' => (string) $recipient['phone'],
                    ':message_text' => $message,
                    ':message_type' => 'event_reminder',
                    ':provider' => 'internal',
                    ':delivery_status' => 'queued',
                    ':event_id' => $eventId,
                    ':sent_by' => $actorId,
                    ':sent_at' => date('Y-m-d H:i:s'),
                ]);
                $smsCount++;
            }
        }

        $emailCount = 0;
        if ($sendEmail) {
            foreach ($recipients as $recipient) {
                if (!empty($recipient['email'])) {
                    $emailCount++;
                }
            }
        }

        Audit::log($this->pdo, $actorId, 'events', 'communicate', 'events', $eventId, null, $input, 'Sent event communication');

        Response::json([
            'success' => true,
            'message' => 'Communication queued',
            'data' => [
                'sms_queued' => $smsCount,
                'email_prepared' => $emailCount,
            ],
        ]);
    }

    public function listGroups(): void
    {
        $rows = $this->pdo->query('SELECT id, name FROM `groups` WHERE is_active = 1 ORDER BY name ASC')->fetchAll();
        Response::json(['success' => true, 'message' => 'Groups list', 'data' => $rows]);
    }

    public function listUsers(): void
    {
        $rows = $this->pdo->query('SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name ASC')->fetchAll();
        Response::json(['success' => true, 'message' => 'Users list', 'data' => $rows]);
    }

    public function eventReport(int $eventId): void
    {
        $this->eventDetails($eventId);
    }

    public function createEventBudgetItem(int $eventId, array $input): void
    {
        $required = ['item_type', 'item_name', 'planned_amount'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $itemType = trim((string) $input['item_type']);
        if (!in_array($itemType, ['income', 'expense'], true)) {
            Response::json(['success' => false, 'message' => 'item_type must be income or expense'], 422);
        }

        $eventExistsStmt = $this->pdo->prepare('SELECT id FROM `events` WHERE id = :id LIMIT 1');
        $eventExistsStmt->execute([':id' => $eventId]);
        if (!$eventExistsStmt->fetch()) {
            Response::json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO event_budget_items (event_id, item_type, item_name, planned_amount, actual_amount, notes)
             VALUES (:event_id, :item_type, :item_name, :planned_amount, :actual_amount, :notes)'
        );
        $stmt->execute([
            ':event_id' => $eventId,
            ':item_type' => $itemType,
            ':item_name' => trim((string) $input['item_name']),
            ':planned_amount' => max(0, (float) $input['planned_amount']),
            ':actual_amount' => max(0, (float) ($input['actual_amount'] ?? 0)),
            ':notes' => trim((string) ($input['notes'] ?? '')),
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        Audit::log($this->pdo, $actorId, 'events', 'budget_item_create', 'event_budget_items', $id, null, $input, 'Created event budget breakdown item');

        Response::json(['success' => true, 'message' => 'Budget item created', 'data' => ['id' => $id]], 201);
    }

    public function updateEventBudgetItem(int $eventId, int $itemId, array $input): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE event_budget_items
             SET item_type = :item_type,
                 item_name = :item_name,
                 planned_amount = :planned_amount,
                 actual_amount = :actual_amount,
                 notes = :notes,
                 updated_at = NOW()
             WHERE id = :id AND event_id = :event_id'
        );

        $itemType = trim((string) ($input['item_type'] ?? 'expense'));
        if (!in_array($itemType, ['income', 'expense'], true)) {
            Response::json(['success' => false, 'message' => 'item_type must be income or expense'], 422);
        }

        $stmt->execute([
            ':id' => $itemId,
            ':event_id' => $eventId,
            ':item_type' => $itemType,
            ':item_name' => trim((string) ($input['item_name'] ?? '')),
            ':planned_amount' => max(0, (float) ($input['planned_amount'] ?? 0)),
            ':actual_amount' => max(0, (float) ($input['actual_amount'] ?? 0)),
            ':notes' => trim((string) ($input['notes'] ?? '')),
        ]);

        if ($stmt->rowCount() === 0) {
            Response::json(['success' => false, 'message' => 'Budget item not found'], 404);
        }

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        Audit::log($this->pdo, $actorId, 'events', 'budget_item_update', 'event_budget_items', $itemId, null, $input, 'Updated event budget item');

        Response::json(['success' => true, 'message' => 'Budget item updated']);
    }

    public function postEventBudgetItemToFinance(int $eventId, int $itemId, array $input): void
    {
        $required = ['category_id', 'amount', 'payment_method'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $budgetItemStmt = $this->pdo->prepare(
            'SELECT id, item_type, item_name, notes
             FROM event_budget_items
             WHERE id = :id AND event_id = :event_id
             LIMIT 1'
        );
        $budgetItemStmt->execute([':id' => $itemId, ':event_id' => $eventId]);
        $budgetItem = $budgetItemStmt->fetch();
        if (!$budgetItem) {
            Response::json(['success' => false, 'message' => 'Budget item not found'], 404);
        }

        $categoryStmt = $this->pdo->prepare('SELECT id, category_type FROM finance_categories WHERE id = :id LIMIT 1');
        $categoryStmt->execute([':id' => (int) $input['category_id']]);
        $category = $categoryStmt->fetch();
        if (!$category) {
            Response::json(['success' => false, 'message' => 'Finance category not found'], 404);
        }
        if ((string) $category['category_type'] !== (string) $budgetItem['item_type']) {
            Response::json(['success' => false, 'message' => 'Finance category type must match budget item type'], 422);
        }

        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $entryNoStmt = $this->pdo->query("SELECT CONCAT('FIN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(COALESCE(MAX(id), 0) + 1, 4, '0')) FROM finance_entries");
        $entryNo = (string) $entryNoStmt->fetchColumn();

        $amount = max(0, (float) $input['amount']);
        if ($amount <= 0) {
            Response::json(['success' => false, 'message' => 'amount must be greater than zero'], 422);
        }

        $entryDate = trim((string) ($input['entry_date'] ?? date('Y-m-d')));
        $description = trim((string) ($input['description'] ?? ''));
        if ($description === '') {
            $description = 'Event budget: ' . (string) $budgetItem['item_name'];
        }

        $hasApprovalCols = $this->columnExists('finance_entries', 'approval_status');

        if ($hasApprovalCols) {
            $insertStmt = $this->pdo->prepare(
                'INSERT INTO finance_entries (
                    entry_no, entry_date, category_id, amount, payment_method,
                    source_type, source_id, event_id, description, recorded_by, approval_status
                ) VALUES (
                    :entry_no, :entry_date, :category_id, :amount, :payment_method,
                    :source_type, :source_id, :event_id, :description, :recorded_by, :approval_status
                )'
            );
            $insertStmt->execute([
                ':entry_no' => $entryNo,
                ':entry_date' => $entryDate,
                ':category_id' => (int) $input['category_id'],
                ':amount' => $amount,
                ':payment_method' => trim((string) $input['payment_method']),
                ':source_type' => 'event',
                ':source_id' => $itemId,
                ':event_id' => $eventId,
                ':description' => $description,
                ':recorded_by' => (int) $user['id'],
                ':approval_status' => 'pending',
            ]);
        } else {
            $insertStmt = $this->pdo->prepare(
                'INSERT INTO finance_entries (
                    entry_no, entry_date, category_id, amount, payment_method,
                    source_type, source_id, event_id, description, recorded_by
                ) VALUES (
                    :entry_no, :entry_date, :category_id, :amount, :payment_method,
                    :source_type, :source_id, :event_id, :description, :recorded_by
                )'
            );
            $insertStmt->execute([
                ':entry_no' => $entryNo,
                ':entry_date' => $entryDate,
                ':category_id' => (int) $input['category_id'],
                ':amount' => $amount,
                ':payment_method' => trim((string) $input['payment_method']),
                ':source_type' => 'event',
                ':source_id' => $itemId,
                ':event_id' => $eventId,
                ':description' => $description,
                ':recorded_by' => (int) $user['id'],
            ]);
        }

        $financeEntryId = (int) $this->pdo->lastInsertId();

        $linkStmt = $this->pdo->prepare(
            'INSERT INTO event_finance_links (event_id, finance_entry_id, relation_type)
             VALUES (:event_id, :finance_entry_id, :relation_type)'
        );
        $linkStmt->execute([
            ':event_id' => $eventId,
            ':finance_entry_id' => $financeEntryId,
            ':relation_type' => (string) $budgetItem['item_type'],
        ]);

        $updateBudgetStmt = $this->pdo->prepare(
            'UPDATE event_budget_items
             SET actual_amount = actual_amount + :amount, updated_at = NOW()
             WHERE id = :id AND event_id = :event_id'
        );
        $updateBudgetStmt->execute([
            ':amount' => $amount,
            ':id' => $itemId,
            ':event_id' => $eventId,
        ]);

        Audit::log(
            $this->pdo,
            (int) $user['id'],
            'events',
            'budget_item_post_finance',
            'finance_entries',
            $financeEntryId,
            null,
            $input,
            'Posted event budget item to finance for accountant approval'
        );

        Response::json([
            'success' => true,
            'message' => 'Budget item posted to finance and is pending accountant approval',
            'data' => ['finance_entry_id' => $financeEntryId],
        ], 201);
    }

    public function sendEventBudgetToFinance(int $eventId): void
    {
        $hasBudgetCols    = $this->columnExists('events', 'budget_status');
        $hasApprovalCols  = $this->columnExists('finance_entries', 'approval_status');

        $budgetStatusSelect = $hasBudgetCols ? ', budget_status' : '';
        $eventStmt = $this->pdo->prepare(
            "SELECT id, title, budget_total{$budgetStatusSelect}, start_datetime
             FROM `events`
             WHERE id = :id
             LIMIT 1"
        );
        $eventStmt->execute([':id' => $eventId]);
        $event = $eventStmt->fetch();
        if (!$event) {
            Response::json(['success' => false, 'message' => 'Event not found'], 404);
        }

        // Determine current budget status — use DB column if exists, otherwise derive from finance_entries
        if ($hasBudgetCols) {
            $currentStatus = $event['budget_status'] ?? 'draft';
        } elseif ($hasApprovalCols) {
            $derivedStmt = $this->pdo->prepare(
                "SELECT CASE approval_status
                     WHEN 'pending'  THEN 'pending_approval'
                     WHEN 'approved' THEN 'approved'
                     WHEN 'rejected' THEN 'rejected'
                     ELSE 'pending_approval'
                 END
                 FROM finance_entries
                 WHERE source_type='event' AND event_id=:eid
                 ORDER BY id DESC LIMIT 1"
            );
            $derivedStmt->execute([':eid' => $eventId]);
            $currentStatus = (string) ($derivedStmt->fetchColumn() ?: 'draft');
        } else {
            $currentStatus = 'draft';
        }
        if ($currentStatus === 'pending_approval') {
            Response::json(['success' => false, 'message' => 'This event budget is already pending approval'], 409);
        }
        if ($currentStatus === 'approved') {
            Response::json(['success' => false, 'message' => 'This event budget has already been approved'], 409);
        }

        $budgetAmount = (float) ($event['budget_total'] ?? 0);
        if ($budgetAmount <= 0) {
            Response::json(['success' => false, 'message' => 'Event has no budget to send'], 422);
        }

        // Check for existing pending entry
        if ($hasApprovalCols) {
            $existsStmt = $this->pdo->prepare(
                "SELECT id FROM finance_entries
                 WHERE source_type = 'event' AND event_id = :eid AND approval_status = 'pending'
                 LIMIT 1"
            );
            $existsStmt->execute([':eid' => $eventId]);
            if ($existsStmt->fetch()) {
                Response::json(['success' => false, 'message' => 'This event already has a pending finance entry'], 409);
            }
        }

        // Prefer the dedicated EVENT_EXPENSE category; fall back to any active expense category
        $categoryStmt = $this->pdo->query(
            "SELECT id FROM finance_categories
             WHERE category_type = 'expense' AND is_active = 1
             ORDER BY (code = 'EVENT_EXPENSE') DESC, is_system DESC, id ASC
             LIMIT 1"
        );
        $categoryId = (int) $categoryStmt->fetchColumn();
        if ($categoryId <= 0) {
            Response::json(['success' => false, 'message' => 'No active expense category found'], 422);
        }

        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $entryNoStmt = $this->pdo->query("SELECT CONCAT('FIN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(COALESCE(MAX(id), 0) + 1, 4, '0')) FROM finance_entries");
        $entryNo = (string) $entryNoStmt->fetchColumn();

        $this->pdo->beginTransaction();
        try {
            // Build description with budget item details
            $itemStmt = $this->pdo->prepare(
                'SELECT item_name, item_type, planned_amount, actual_amount
                 FROM event_budget_items WHERE event_id = :eid ORDER BY id ASC'
            );
            $itemStmt->execute([':eid' => $eventId]);
            $items = $itemStmt->fetchAll();
            $desc = 'Event budget: ' . (string) $event['title'];
            if (!empty($items)) {
                $itemList = array_map(fn($i) => $i['item_name'] . ' (TZS ' . number_format((float)$i['planned_amount'], 0) . ')', $items);
                $desc .= ' | Items: ' . implode(', ', array_slice($itemList, 0, 5));
                if (count($itemList) > 5) $desc .= '... +' . (count($itemList) - 5) . ' more';
            }

            // Insert finance entry
            if ($hasApprovalCols) {
                $insertStmt = $this->pdo->prepare(
                    'INSERT INTO finance_entries (
                        entry_no, entry_date, category_id, amount, payment_method,
                        source_type, source_id, event_id, description, recorded_by, approval_status
                    ) VALUES (
                        :entry_no, :entry_date, :category_id, :amount, :payment_method,
                        :source_type, :source_id, :event_id, :description, :recorded_by, :approval_status
                    )'
                );
                $insertStmt->execute([
                    ':entry_no' => $entryNo,
                    ':entry_date' => date('Y-m-d'),
                    ':category_id' => $categoryId,
                    ':amount' => $budgetAmount,
                    ':payment_method' => 'cash',
                    ':source_type' => 'event',
                    ':source_id' => $eventId,
                    ':event_id' => $eventId,
                    ':description' => $desc,
                    ':recorded_by' => (int) $user['id'],
                    ':approval_status' => 'pending',
                ]);
            } else {
                $insertStmt = $this->pdo->prepare(
                    'INSERT INTO finance_entries (
                        entry_no, entry_date, category_id, amount, payment_method,
                        source_type, source_id, event_id, description, recorded_by
                    ) VALUES (
                        :entry_no, :entry_date, :category_id, :amount, :payment_method,
                        :source_type, :source_id, :event_id, :description, :recorded_by
                    )'
                );
                $insertStmt->execute([
                    ':entry_no' => $entryNo,
                    ':entry_date' => date('Y-m-d'),
                    ':category_id' => $categoryId,
                    ':amount' => $budgetAmount,
                    ':payment_method' => 'cash',
                    ':source_type' => 'event',
                    ':source_id' => $eventId,
                    ':event_id' => $eventId,
                    ':description' => $desc,
                    ':recorded_by' => (int) $user['id'],
                ]);
            }
            $financeEntryId = (int) $this->pdo->lastInsertId();

            // Link event to finance entry
            $linkStmt = $this->pdo->prepare(
                'INSERT INTO event_finance_links (event_id, finance_entry_id, relation_type)
                 VALUES (:event_id, :finance_entry_id, :relation_type)'
            );
            $linkStmt->execute([
                ':event_id' => $eventId,
                ':finance_entry_id' => $financeEntryId,
                ':relation_type' => 'expense',
            ]);

            // Update event budget status (only if column exists)
            if ($hasBudgetCols) {
                $updateStmt = $this->pdo->prepare(
                    "UPDATE `events` SET budget_status = 'pending_approval' WHERE id = :id"
                );
                $updateStmt->execute([':id' => $eventId]);
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed to send budget: ' . $e->getMessage()], 500);
        }

        Audit::log(
            $this->pdo,
            (int) $user['id'],
            'events',
            'send_budget_to_finance',
            'finance_entries',
            $financeEntryId,
            null,
            ['event_id' => $eventId, 'amount' => $budgetAmount, 'items_count' => count($items ?? [])],
            'Sent event budget to finance for approval'
        );

        Response::json([
            'success' => true,
            'message' => 'Budget sent to finance for approval',
            'data' => ['finance_entry_id' => $financeEntryId, 'budget_status' => 'pending_approval'],
        ], 201);
    }

    public function registerEventParticipant(int $eventId, array $input): void
    {
        $memberId = isset($input['member_id']) ? (int) $input['member_id'] : 0;
        if ($memberId <= 0) {
            Response::json(['success' => false, 'message' => 'member_id is required'], 422);
        }

        $memberStmt = $this->pdo->prepare('SELECT id FROM members WHERE id = :id LIMIT 1');
        $memberStmt->execute([':id' => $memberId]);
        if (!$memberStmt->fetch()) {
            Response::json(['success' => false, 'message' => 'Member not found'], 404);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO event_attendance (event_id, member_id, status)
             VALUES (:event_id, :member_id, :status)
             ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()'
        );
        $stmt->execute([
            ':event_id' => $eventId,
            ':member_id' => $memberId,
            ':status' => 'registered',
        ]);

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        Audit::log($this->pdo, $actorId, 'events', 'attendance_register', 'event_attendance', $memberId, null, $input, 'Registered event participant');

        Response::json(['success' => true, 'message' => 'Participant registered']);
    }

    public function updateEventParticipantAttendance(int $eventId, int $attendanceId, array $input): void
    {
        $status = trim((string) ($input['status'] ?? ''));
        if (!in_array($status, ['registered', 'present', 'absent'], true)) {
            Response::json(['success' => false, 'message' => 'status must be registered, present or absent'], 422);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE event_attendance
             SET status = :status,
                 check_in_datetime = :check_in_datetime,
                 updated_at = NOW()
             WHERE id = :id AND event_id = :event_id'
        );
        $stmt->execute([
            ':status' => $status,
            ':check_in_datetime' => $status === 'present' ? date('Y-m-d H:i:s') : null,
            ':id' => $attendanceId,
            ':event_id' => $eventId,
        ]);

        if ($stmt->rowCount() === 0) {
            Response::json(['success' => false, 'message' => 'Attendance row not found'], 404);
        }

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        Audit::log($this->pdo, $actorId, 'events', 'attendance_update', 'event_attendance', $attendanceId, null, $input, 'Updated event participant attendance status');

        Response::json(['success' => true, 'message' => 'Attendance status updated']);
    }

    /* ───── Attendance ───── */

    public function attendanceOverview(): void
    {
        $this->ensureAttendanceSnapshotsTable();

        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            Response::json(['success' => false, 'message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $monthlyStmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS services_count,
                COALESCE(SUM(men_count), 0) AS men_total,
                COALESCE(SUM(women_count), 0) AS women_total,
                COALESCE(SUM(children_count), 0) AS children_total,
                COALESCE(SUM(youth_count), 0) AS youth_total,
                COALESCE(SUM(guests_count), 0) AS guests_total,
                COALESCE(SUM(total_count), 0) AS attendance_total
             FROM attendance_snapshots
             WHERE service_date BETWEEN :start_at AND :end_at"
        );
        $monthlyStmt->execute([
            ':start_at' => $monthStart,
            ':end_at' => $monthEnd,
        ]);
        $monthly = $monthlyStmt->fetch() ?: [];

        $latestStmt = $this->pdo->query(
            'SELECT id, service_date, service_name, service_type,
                    men_count, women_count, children_count, youth_count, guests_count, total_count,
                    notes, created_at
             FROM attendance_snapshots
             ORDER BY service_date DESC, id DESC
             LIMIT 1'
        );
        $latest = $latestStmt->fetch() ?: null;

        $trendStmt = $this->pdo->prepare(
            'SELECT service_date, total_count
             FROM attendance_snapshots
             WHERE service_date BETWEEN :start_at AND :end_at
             ORDER BY service_date ASC, id ASC'
        );
        $trendStmt->execute([
            ':start_at' => $monthStart,
            ':end_at' => $monthEnd,
        ]);
        $trendRows = $trendStmt->fetchAll();

        Response::json([
            'success' => true,
            'message' => 'Attendance overview',
            'data' => [
                'month' => $month,
                'summary' => [
                    'services_count' => (int) ($monthly['services_count'] ?? 0),
                    'men_total' => (int) ($monthly['men_total'] ?? 0),
                    'women_total' => (int) ($monthly['women_total'] ?? 0),
                    'children_total' => (int) ($monthly['children_total'] ?? 0),
                    'youth_total' => (int) ($monthly['youth_total'] ?? 0),
                    'guests_total' => (int) ($monthly['guests_total'] ?? 0),
                    'attendance_total' => (int) ($monthly['attendance_total'] ?? 0),
                ],
                'latest' => $latest,
                'trend' => $trendRows,
            ],
        ]);
    }

    public function listAttendanceSnapshots(): void
    {
        $this->ensureAttendanceSnapshotsTable();

        $month = trim((string) ($_GET['month'] ?? ''));
        $type = trim((string) ($_GET['type'] ?? ''));

        $sql = 'SELECT id, service_date, service_name, service_type,
                       men_count, women_count, children_count, youth_count, guests_count,
                       total_count, notes, created_at
                FROM attendance_snapshots
                WHERE 1=1';
        $params = [];

        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $sql .= ' AND DATE_FORMAT(service_date, "%Y-%m") = :month';
            $params[':month'] = $month;
        }

        if ($type !== '') {
            $sql .= ' AND service_type = :type';
            $params[':type'] = $type;
        }

        $sql .= ' ORDER BY service_date DESC, id DESC LIMIT 300';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        Response::json([
            'success' => true,
            'message' => 'Attendance snapshots',
            'data' => $stmt->fetchAll(),
        ]);
    }

    public function recordAttendanceSnapshot(array $input): void
    {
        if (!Auth::can('attendance.record')) {
            Response::json(['success' => false, 'message' => 'No permission to record attendance'], 403); return;
        }
        $this->ensureAttendanceSnapshotsTable();

        $required = ['service_date', 'service_name'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $serviceDate = trim((string) $input['service_date']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $serviceDate) !== 1) {
            Response::json(['success' => false, 'message' => 'service_date must be YYYY-MM-DD'], 422);
        }

        $serviceType = trim((string) ($input['service_type'] ?? 'sunday_service'));
        $allowedTypes = ['sunday_service', 'midweek', 'prayer', 'youth_service', 'special', 'other'];
        if (!in_array($serviceType, $allowedTypes, true)) {
            $serviceType = 'other';
        }

        $men = max(0, (int) ($input['men_count'] ?? 0));
        $women = max(0, (int) ($input['women_count'] ?? 0));
        $children = max(0, (int) ($input['children_count'] ?? 0));
        $youth = max(0, (int) ($input['youth_count'] ?? 0));
        $guests = max(0, (int) ($input['guests_count'] ?? 0));
        $total = $men + $women + $children + $youth + $guests;

        if ($total <= 0) {
            Response::json(['success' => false, 'message' => 'Enter at least one attendance value'], 422);
        }

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO attendance_snapshots (
                service_date, service_name, service_type,
                men_count, women_count, children_count, youth_count, guests_count,
                total_count, notes, created_by
             ) VALUES (
                :service_date, :service_name, :service_type,
                :men_count, :women_count, :children_count, :youth_count, :guests_count,
                :total_count, :notes, :created_by
             )'
        );
        $stmt->execute([
            ':service_date' => $serviceDate,
            ':service_name' => trim((string) $input['service_name']),
            ':service_type' => $serviceType,
            ':men_count' => $men,
            ':women_count' => $women,
            ':children_count' => $children,
            ':youth_count' => $youth,
            ':guests_count' => $guests,
            ':total_count' => $total,
            ':notes' => trim((string) ($input['notes'] ?? '')),
            ':created_by' => $actorId,
        ]);

        $snapshotId = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, $actorId, 'attendance', 'create', 'attendance_snapshots', $snapshotId, null, [
            'service_date' => $serviceDate,
            'service_name' => trim((string) $input['service_name']),
            'service_type' => $serviceType,
            'total_count' => $total,
        ], 'Recorded aggregate attendance snapshot');

        Response::json([
            'success' => true,
            'message' => 'Attendance recorded',
            'data' => [
                'id' => $snapshotId,
                'total_count' => $total,
            ],
        ], 201);
    }

    private function ensureAttendanceSnapshotsTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS attendance_snapshots (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                service_date DATE NOT NULL,
                service_name VARCHAR(150) NOT NULL,
                service_type ENUM("sunday_service", "midweek", "prayer", "youth_service", "special", "other") NOT NULL DEFAULT "sunday_service",
                men_count INT UNSIGNED NOT NULL DEFAULT 0,
                women_count INT UNSIGNED NOT NULL DEFAULT 0,
                children_count INT UNSIGNED NOT NULL DEFAULT 0,
                youth_count INT UNSIGNED NOT NULL DEFAULT 0,
                guests_count INT UNSIGNED NOT NULL DEFAULT 0,
                total_count INT UNSIGNED NOT NULL DEFAULT 0,
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_attendance_snapshots_date (service_date),
                INDEX idx_attendance_snapshots_type (service_type),
                CONSTRAINT fk_attendance_snapshots_created_by FOREIGN KEY (created_by) REFERENCES users(id)
                    ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function registerGuest(array $input): void
    {
        $this->ensureGuestsTable();

        $required = ['first_name', 'last_name', 'phone', 'location', 'service_date'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::json(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 422);
                return;
            }
        }

        $serviceDate = trim((string) $input['service_date']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $serviceDate) !== 1) {
            Response::json(['success' => false, 'message' => 'service_date must be YYYY-MM-DD'], 422);
            return;
        }

        $firstName = trim((string) $input['first_name']);
        $lastName = trim((string) $input['last_name']);
        $phone = trim((string) $input['phone']);
        $location = trim((string) $input['location']);
        $email = trim((string) ($input['email'] ?? ''));
        $ageGroup = trim((string) ($input['age_group'] ?? ''));
        $visitType = trim((string) ($input['visit_type'] ?? 'first_time'));
        $invitedByName = trim((string) ($input['invited_by_name'] ?? ''));
        $followUpDate = trim((string) ($input['follow_up_date'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));

        $allowedVisitTypes = ['first_time', 'returning', 'referred'];
        if (!in_array($visitType, $allowedVisitTypes, true)) {
            $visitType = 'first_time';
        }

        $allowedAgeGroups = ['child', 'teen', 'youth', 'adult', 'senior'];
        if ($ageGroup && !in_array($ageGroup, $allowedAgeGroups, true)) {
            $ageGroup = null;
        }

        // Generate unique guest code
        $year = (int) date('Y');
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM guests WHERE guest_code LIKE ?');
        $countStmt->execute(["GU-$year-%"]);
        $count = ((int) $countStmt->fetchColumn()) + 1;
        $guestCode = "GU-$year-" . str_pad((string)$count, 5, '0', STR_PAD_LEFT);

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO guests (
                guest_code, first_name, last_name, phone, location, email,
                age_group, visit_type, invited_by_name, service_date, 
                follow_up_date, notes, status, created_by
             ) VALUES (
                :guest_code, :first_name, :last_name, :phone, :location, :email,
                :age_group, :visit_type, :invited_by_name, :service_date,
                :follow_up_date, :notes, :status, :created_by
             )'
        );

        $stmt->execute([
            ':guest_code' => $guestCode,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':phone' => $phone,
            ':location' => $location,
            ':email' => $email ?: null,
            ':age_group' => $ageGroup ?: null,
            ':visit_type' => $visitType,
            ':invited_by_name' => $invitedByName ?: null,
            ':service_date' => $serviceDate,
            ':follow_up_date' => $followUpDate ?: null,
            ':notes' => $notes,
            ':status' => 'registered',
            ':created_by' => $actorId,
        ]);

        $guestId = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, $actorId, 'attendance', 'create', 'guests', $guestId, null, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'location' => $location,
            'visit_type' => $visitType,
        ], "Registered guest: $firstName $lastName");

        Response::json([
            'success' => true,
            'message' => 'Guest registered successfully',
            'data' => [
                'id' => $guestId,
                'guest_code' => $guestCode,
            ],
        ], 201);
    }

    public function getGuests(): void
    {
        $this->ensureGuestsTable();

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $sortBy = trim((string) ($_GET['sort'] ?? 'service_date'));
        $sortOrder = trim((string) ($_GET['order'] ?? 'DESC'));

        // Validate sort parameters
        $allowedSortFields = ['guest_code', 'first_name', 'last_name', 'phone', 'service_date', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 'service_date';
        }
        if (!in_array(strtoupper($sortOrder), ['ASC', 'DESC'], true)) {
            $sortOrder = 'DESC';
        }

        $query = 'SELECT 
                    id, guest_code, first_name, last_name, phone, location, 
                    email, age_group, visit_type, invited_by_name, service_date, 
                    follow_up_date, notes, status, created_by, created_at, updated_at
                  FROM guests
                  WHERE 1=1';

        $params = [];

        // Search filter
        if ($search) {
            $query .= ' AND (
                LOWER(guest_code) LIKE LOWER(?) OR 
                LOWER(first_name) LIKE LOWER(?) OR 
                LOWER(last_name) LIKE LOWER(?) OR 
                LOWER(phone) LIKE LOWER(?) OR 
                LOWER(email) LIKE LOWER(?) OR
                LOWER(location) LIKE LOWER(?)
            )';
            $searchTerm = "%$search%";
            $params = array_fill(0, 6, $searchTerm);
        }

        // Status filter
        if ($status) {
            $allowedStatuses = ['registered', 'visited', 'converted', 'inactive'];
            if (in_array($status, $allowedStatuses, true)) {
                $query .= ' AND status = ?';
                $params[] = $status;
            }
        }

        $query .= " ORDER BY $sortBy $sortOrder";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $guests = $stmt->fetchAll();

            Response::json([
                'success' => true,
                'data' => $guests ?: [],
                'count' => count($guests),
            ]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Failed to fetch guests'], 500);
        }
    }

    private function ensureGuestsTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS guests (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                guest_code VARCHAR(50) NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                location VARCHAR(255) NOT NULL,
                invited_by_member_id BIGINT UNSIGNED NULL,
                invited_by_name VARCHAR(100) NULL,
                service_date DATE NOT NULL,
                visit_type ENUM("first_time", "returning", "referred") NOT NULL DEFAULT "first_time",
                email VARCHAR(150) NULL,
                age_group ENUM("child", "teen", "youth", "adult", "senior") NULL,
                notes TEXT NULL,
                status ENUM("registered", "visited", "converted", "inactive") NOT NULL DEFAULT "registered",
                follow_up_date DATE NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                CONSTRAINT fk_guests_invited_by FOREIGN KEY (invited_by_member_id) REFERENCES members(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_guests_created_by FOREIGN KEY (created_by) REFERENCES users(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                
                INDEX idx_guests_phone (phone),
                INDEX idx_guests_service_date (service_date),
                INDEX idx_guests_status (status),
                INDEX idx_guests_location (location)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /* ───── Assets ───── */

    public function assetsOverview(): void
    {
        $totals = $this->pdo->query(
            "SELECT
                COUNT(*) AS total_assets,
                COALESCE(SUM(COALESCE(purchase_value, 0)), 0) AS total_value,
                SUM(condition_status = 'excellent') AS excellent_count,
                SUM(condition_status = 'good') AS good_count,
                SUM(condition_status = 'fair') AS fair_count,
                SUM(condition_status = 'poor') AS poor_count,
                SUM(condition_status = 'retired') AS retired_count,
                SUM(is_active = 1) AS active_count
             FROM assets"
        )->fetch() ?: [];

        $dueStmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM maintenance_logs
             WHERE next_due_date IS NOT NULL
               AND next_due_date <= CURRENT_DATE"
        );
        $dueStmt->execute();
        $dueMaintenance = (int) $dueStmt->fetchColumn();

        Response::json([
            'success' => true,
            'message' => 'Assets overview',
            'data' => [
                'total_assets' => (int) ($totals['total_assets'] ?? 0),
                'active_count' => (int) ($totals['active_count'] ?? 0),
                'total_value' => (float) ($totals['total_value'] ?? 0),
                'conditions' => [
                    'excellent' => (int) ($totals['excellent_count'] ?? 0),
                    'good' => (int) ($totals['good_count'] ?? 0),
                    'fair' => (int) ($totals['fair_count'] ?? 0),
                    'poor' => (int) ($totals['poor_count'] ?? 0),
                    'retired' => (int) ($totals['retired_count'] ?? 0),
                ],
                'due_maintenance' => $dueMaintenance,
            ],
        ]);
    }

    public function listAssets(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));
        $condition = trim((string) ($_GET['condition'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));
        $departmentId = trim((string) ($_GET['department_id'] ?? ''));

        $hasDeptCol = $this->columnExists('asset_assignments', 'assigned_department_id');
        $deptJoin = $hasDeptCol
            ? 'LEFT JOIN asset_assignments da ON da.asset_id = a.id AND da.assigned_type = "department" AND da.assigned_to IS NULL LEFT JOIN departments dept ON dept.id = da.assigned_department_id'
            : '';
        $deptSelect = $hasDeptCol ? ', da.assigned_department_id, dept.name AS department_name' : ', NULL AS assigned_department_id, NULL AS department_name';

        $sql = "SELECT a.id, a.asset_tag, a.name, a.category, a.purchase_date, a.purchase_value,
                       a.condition_status, a.current_location, a.assigned_to_user_id, a.assigned_event_id,
                       a.warranty_expiry, a.is_active, a.notes,
                       u.full_name AS assigned_user_name,
                       e.title AS assigned_event_title,
                       lm.latest_maintenance_date,
                       lm.latest_next_due_date,
                       lm.latest_maintenance_cost
                       $deptSelect
                FROM assets a
                LEFT JOIN users u ON u.id = a.assigned_to_user_id
                LEFT JOIN `events` e ON e.id = a.assigned_event_id
                $deptJoin
                LEFT JOIN (
                    SELECT ml.asset_id,
                           MAX(ml.maintenance_date) AS latest_maintenance_date,
                           SUBSTRING_INDEX(GROUP_CONCAT(ml.next_due_date ORDER BY ml.maintenance_date DESC), ',', 1) AS latest_next_due_date,
                           SUBSTRING_INDEX(GROUP_CONCAT(ml.maintenance_cost ORDER BY ml.maintenance_date DESC), ',', 1) AS latest_maintenance_cost
                    FROM maintenance_logs ml
                    GROUP BY ml.asset_id
                ) lm ON lm.asset_id = a.id
                WHERE 1=1";

        $params = [];

        if ($search !== '') {
            $sql .= ' AND (a.asset_tag LIKE :s1 OR a.name LIKE :s2 OR a.current_location LIKE :s3)';
            $like = '%' . $search . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
        }

        if ($condition !== '' && in_array($condition, ['excellent', 'good', 'fair', 'poor', 'retired'], true)) {
            $sql .= ' AND a.condition_status = :condition';
            $params[':condition'] = $condition;
        }

        if ($category !== '') {
            $sql .= ' AND a.category = :category';
            $params[':category'] = $category;
        }

        if ($departmentId !== '' && ctype_digit($departmentId) && $hasDeptCol) {
            $sql .= ' AND da.assigned_department_id = :dept_id';
            $params[':dept_id'] = (int) $departmentId;
        }

        $sql .= ' ORDER BY a.created_at DESC, a.id DESC LIMIT 500';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        Response::json([
            'success' => true,
            'message' => 'Assets list',
            'data' => $stmt->fetchAll(),
        ]);
    }

    public function createAsset(array $input): void
    {
        if (!Auth::can('assets.create')) {
            Response::json(['success' => false, 'message' => 'No permission to create assets'], 403); return;
        }
        $required = ['name', 'category', 'current_location'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $assetTag = trim((string) ($input['asset_tag'] ?? ''));
        if ($assetTag === '') {
            $tagStmt = $this->pdo->query(
                "SELECT CONCAT('AST-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(COALESCE(MAX(id), 0) + 1, 4, '0')) FROM assets"
            );
            $assetTag = (string) $tagStmt->fetchColumn();
        }

        $condition = trim((string) ($input['condition_status'] ?? 'good'));
        if (!in_array($condition, ['excellent', 'good', 'fair', 'poor', 'retired'], true)) {
            $condition = 'good';
        }

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO assets (
                asset_tag, name, category, purchase_date, purchase_value,
                condition_status, current_location, assigned_to_user_id, assigned_event_id,
                warranty_expiry, is_active, notes
            ) VALUES (
                :asset_tag, :name, :category, :purchase_date, :purchase_value,
                :condition_status, :current_location, :assigned_to_user_id, :assigned_event_id,
                :warranty_expiry, :is_active, :notes
            )'
        );

        $stmt->execute([
            ':asset_tag' => $assetTag,
            ':name' => trim((string) $input['name']),
            ':category' => trim((string) $input['category']),
            ':purchase_date' => !empty($input['purchase_date']) ? $input['purchase_date'] : null,
            ':purchase_value' => isset($input['purchase_value']) && $input['purchase_value'] !== '' ? (float) $input['purchase_value'] : null,
            ':condition_status' => $condition,
            ':current_location' => trim((string) $input['current_location']),
            ':assigned_to_user_id' => isset($input['assigned_to_user_id']) && $input['assigned_to_user_id'] !== '' ? (int) $input['assigned_to_user_id'] : null,
            ':assigned_event_id' => isset($input['assigned_event_id']) && $input['assigned_event_id'] !== '' ? (int) $input['assigned_event_id'] : null,
            ':warranty_expiry' => !empty($input['warranty_expiry']) ? $input['warranty_expiry'] : null,
            ':is_active' => isset($input['is_active']) ? (int) ((int) $input['is_active'] === 1) : 1,
            ':notes' => trim((string) ($input['notes'] ?? '')),
        ]);

        $assetId = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, $actorId, 'assets', 'create', 'assets', $assetId, null, [
            'asset_tag' => $assetTag,
            'name' => trim((string) $input['name']),
        ], 'Created asset record');

        Response::json([
            'success' => true,
            'message' => 'Asset created',
            'data' => ['id' => $assetId, 'asset_tag' => $assetTag],
        ], 201);
    }

    public function updateAsset(int $assetId, array $input): void
    {
        if (!Auth::can('assets.edit')) {
            Response::json(['success' => false, 'message' => 'No permission to edit assets'], 403); return;
        }
        $allowed = [
            'name', 'category', 'purchase_date', 'purchase_value', 'condition_status', 'current_location',
            'assigned_to_user_id', 'assigned_event_id', 'warranty_expiry', 'is_active', 'notes',
        ];

        $set = [];
        $params = [':id' => $assetId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                $set[] = "`$field` = :$field";

                $value = $input[$field];
                if (in_array($field, ['assigned_to_user_id', 'assigned_event_id'], true)) {
                    $value = ($value !== '' && $value !== null) ? (int) $value : null;
                } elseif ($field === 'is_active') {
                    $value = (int) ((int) $value === 1);
                } elseif ($field === 'purchase_value' && $value !== '' && $value !== null) {
                    $value = (float) $value;
                } elseif ($value === '') {
                    $value = null;
                }

                $params[":" . $field] = $value;
            }
        }

        if (empty($set)) {
            Response::json(['success' => false, 'message' => 'Nothing to update'], 422);
        }

        $stmt = $this->pdo->prepare('UPDATE assets SET ' . implode(', ', $set) . ' WHERE id = :id');
        $stmt->execute($params);

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        Audit::log($this->pdo, $actorId, 'assets', 'update', 'assets', $assetId, null, $input, 'Updated asset record');

        Response::json(['success' => true, 'message' => 'Asset updated']);
    }

    public function listAssetMaintenance(int $assetId): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT ml.id, ml.maintenance_type, ml.issue_description, ml.action_taken, ml.service_provider,
                    ml.maintenance_cost, ml.maintenance_date, ml.next_due_date,
                    u.full_name AS created_by_name
             FROM maintenance_logs ml
             LEFT JOIN users u ON u.id = ml.created_by
             WHERE ml.asset_id = :asset_id
             ORDER BY ml.maintenance_date DESC, ml.id DESC
             LIMIT 200'
        );
        $stmt->execute([':asset_id' => $assetId]);

        Response::json([
            'success' => true,
            'message' => 'Asset maintenance logs',
            'data' => $stmt->fetchAll(),
        ]);
    }

    public function createAssetMaintenance(int $assetId, array $input): void
    {
        $required = ['maintenance_type', 'action_taken', 'maintenance_date'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
            }
        }

        $maintenanceType = trim((string) $input['maintenance_type']);
        if (!in_array($maintenanceType, ['routine', 'repair', 'inspection', 'replacement'], true)) {
            Response::json(['success' => false, 'message' => 'Invalid maintenance_type'], 422);
        }

        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO maintenance_logs (
                asset_id, maintenance_type, issue_description, action_taken,
                service_provider, maintenance_cost, maintenance_date, next_due_date, created_by
             ) VALUES (
                :asset_id, :maintenance_type, :issue_description, :action_taken,
                :service_provider, :maintenance_cost, :maintenance_date, :next_due_date, :created_by
             )'
        );
        $stmt->execute([
            ':asset_id' => $assetId,
            ':maintenance_type' => $maintenanceType,
            ':issue_description' => trim((string) ($input['issue_description'] ?? '')),
            ':action_taken' => trim((string) $input['action_taken']),
            ':service_provider' => trim((string) ($input['service_provider'] ?? '')),
            ':maintenance_cost' => isset($input['maintenance_cost']) && $input['maintenance_cost'] !== '' ? (float) $input['maintenance_cost'] : 0,
            ':maintenance_date' => $input['maintenance_date'],
            ':next_due_date' => !empty($input['next_due_date']) ? $input['next_due_date'] : null,
            ':created_by' => $actorId,
        ]);

        if (!empty($input['condition_status']) && in_array((string) $input['condition_status'], ['excellent', 'good', 'fair', 'poor', 'retired'], true)) {
            $assetUpdateStmt = $this->pdo->prepare('UPDATE assets SET condition_status = :condition_status WHERE id = :asset_id');
            $assetUpdateStmt->execute([
                ':condition_status' => (string) $input['condition_status'],
                ':asset_id' => $assetId,
            ]);
        }

        $maintenanceId = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, $actorId, 'assets', 'maintenance', 'maintenance_logs', $maintenanceId, null, [
            'asset_id' => $assetId,
            'maintenance_type' => $maintenanceType,
        ], 'Recorded asset maintenance log');

        Response::json([
            'success' => true,
            'message' => 'Maintenance log created',
            'data' => ['id' => $maintenanceId],
        ], 201);
    }

    /* ───── Asset Assignment ───── */

    public function assignAsset(int $assetId, array $input): void
    {
        if (!Auth::can('assets.edit')) {
            Response::json(['success' => false, 'message' => 'No permission'], 403); return;
        }
        $type = trim((string) ($input['assigned_type'] ?? ''));
        if (!in_array($type, ['user', 'event', 'department', 'location'], true)) {
            Response::json(['success' => false, 'message' => 'assigned_type must be user, event, department or location'], 422); return;
        }

        $user = Auth::user();
        $actorId = (int) ($user['id'] ?? 0);

        // Close any current open assignment of the same type (or all if requested)
        $this->pdo->prepare(
            'UPDATE asset_assignments SET assigned_to = NOW() WHERE asset_id = :aid AND assigned_to IS NULL'
        )->execute([':aid' => $assetId]);

        $hasDeptCol = $this->columnExists('asset_assignments', 'assigned_department_id');

        $cols = 'asset_id, assigned_type, assigned_user_id, assigned_event_id, assigned_location, assigned_from, assigned_by, notes';
        $vals = ':aid, :type, :uid, :eid, :loc, NOW(), :by, :notes';
        $params = [
            ':aid'   => $assetId,
            ':type'  => $type,
            ':uid'   => $type === 'user' && !empty($input['assigned_user_id']) ? (int) $input['assigned_user_id'] : null,
            ':eid'   => $type === 'event' && !empty($input['assigned_event_id']) ? (int) $input['assigned_event_id'] : null,
            ':loc'   => $type === 'location' ? trim((string) ($input['assigned_location'] ?? '')) : null,
            ':by'    => $actorId,
            ':notes' => trim((string) ($input['notes'] ?? '')),
        ];

        if ($hasDeptCol) {
            $cols .= ', assigned_department_id';
            $vals .= ', :did';
            $params[':did'] = $type === 'department' && !empty($input['department_id']) ? (int) $input['department_id'] : null;
        }

        $this->pdo->prepare("INSERT INTO asset_assignments ($cols) VALUES ($vals)")->execute($params);

        // Update assets table quick-assign fields for user/event
        if ($type === 'user') {
            $this->pdo->prepare('UPDATE assets SET assigned_to_user_id = :uid, assigned_event_id = NULL WHERE id = :id')
                ->execute([':uid' => (int) ($input['assigned_user_id'] ?? 0), ':id' => $assetId]);
        } elseif ($type === 'event') {
            $this->pdo->prepare('UPDATE assets SET assigned_event_id = :eid, assigned_to_user_id = NULL WHERE id = :id')
                ->execute([':eid' => (int) ($input['assigned_event_id'] ?? 0), ':id' => $assetId]);
        } elseif ($type === 'department') {
            $this->pdo->prepare('UPDATE assets SET assigned_to_user_id = NULL, assigned_event_id = NULL WHERE id = :id')
                ->execute([':id' => $assetId]);
        }

        Audit::log($this->pdo, $actorId, 'assets', 'assign', 'asset_assignments', (int) $this->pdo->lastInsertId(), null, $input, 'Asset assigned');
        Response::json(['success' => true, 'message' => 'Asset assigned successfully']);
    }

    public function unassignAsset(int $assetId): void
    {
        if (!Auth::can('assets.edit')) {
            Response::json(['success' => false, 'message' => 'No permission'], 403); return;
        }

        $stmt = $this->pdo->prepare('UPDATE asset_assignments SET assigned_to = NOW() WHERE asset_id = :aid AND assigned_to IS NULL');
        $stmt->execute([':aid' => $assetId]);

        $this->pdo->prepare('UPDATE assets SET assigned_to_user_id = NULL, assigned_event_id = NULL WHERE id = :id')
            ->execute([':id' => $assetId]);

        Response::json(['success' => true, 'message' => 'Asset unassigned']);
    }

    public function listAssetAssignments(int $assetId): void
    {
        $hasDeptCol = $this->columnExists('asset_assignments', 'assigned_department_id');
        $deptJoin = $hasDeptCol ? 'LEFT JOIN departments d ON d.id = aa.assigned_department_id' : '';
        $deptCol  = $hasDeptCol ? ', d.name AS department_name' : ", NULL AS department_name";

        $stmt = $this->pdo->prepare(
            "SELECT aa.id, aa.assigned_type, aa.assigned_from, aa.assigned_to, aa.notes,
                    u.full_name AS user_name, e.title AS event_title,
                    aa.assigned_location $deptCol,
                    ab.full_name AS assigned_by_name
             FROM asset_assignments aa
             LEFT JOIN users u ON u.id = aa.assigned_user_id
             LEFT JOIN events e ON e.id = aa.assigned_event_id
             LEFT JOIN users ab ON ab.id = aa.assigned_by
             $deptJoin
             WHERE aa.asset_id = :aid
             ORDER BY aa.assigned_from DESC"
        );
        $stmt->execute([':aid' => $assetId]);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    /* ───── Finance ───── */

    public function listFinanceEntries(): void
    {
        $rows = $this->pdo->query(
            'SELECT fe.id, fe.entry_no, fe.entry_date, fc.name AS category_name, fc.category_type,
                    fe.amount, fe.payment_method, fe.description
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id = fe.category_id
             ORDER BY fe.entry_date DESC, fe.id DESC LIMIT 200'
        )->fetchAll();
        Response::json(['success' => true, 'message' => 'Finance entries', 'data' => $rows]);
    }

    public function listFinanceCategories(): void
    {
        $rows = $this->pdo->query('SELECT id, name, category_type FROM finance_categories ORDER BY category_type, name')->fetchAll();
        Response::json(['success' => true, 'message' => 'Finance categories', 'data' => $rows]);
    }

    public function createFinanceEntry(array $input): void
    {
        if (!Auth::can('finance.create')) {
            Response::json(['success' => false, 'message' => 'No permission to create finance entries'], 403); return;
        }

        $required = ['entry_date', 'category_id', 'amount', 'payment_method', 'description'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || (string) $input[$field] === '') {
                Response::json(['success' => false, 'message' => $field . ' is required'], 422);
                return;
            }
        }

        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
            return;
        }

        // Auto-generate entry_no if not provided
        $entryNo = trim((string) ($input['entry_no'] ?? ''));
        if ($entryNo === '') {
            $seq = (int) $this->pdo->query("SELECT COUNT(*) + 1 FROM finance_entries WHERE entry_date = '{$input['entry_date']}'")->fetchColumn();
            $entryNo = 'FIN-' . str_replace('-', '', $input['entry_date']) . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        }

        $hasAccountCol      = $this->columnExists('finance_entries', 'church_account_id');
        $hasApprovalCol     = $this->columnExists('finance_entries', 'approval_status');
        $accountCol         = $hasAccountCol ? ', church_account_id' : '';
        $accountPlaceholder = $hasAccountCol ? ', :church_account_id' : '';
        $approvalCol        = $hasApprovalCol ? ', approval_status' : '';
        $approvalPlaceholder = $hasApprovalCol ? ', :approval_status' : '';

        $stmt = $this->pdo->prepare(
            "INSERT INTO finance_entries (entry_no, entry_date, category_id, amount, payment_method, source_type, source_id, event_id, member_id, supplier_id, purchase_order_id, description, recorded_by{$accountCol}{$approvalCol})
             VALUES (:entry_no, :entry_date, :category_id, :amount, :payment_method, :source_type, :source_id, :event_id, :member_id, :supplier_id, :purchase_order_id, :description, :recorded_by{$accountPlaceholder}{$approvalPlaceholder})"
        );

        $params = [
            ':entry_no'          => $entryNo,
            ':entry_date'        => $input['entry_date'],
            ':category_id'       => (int) $input['category_id'],
            ':amount'            => (float) $input['amount'],
            ':payment_method'    => $input['payment_method'],
            ':source_type'       => $input['source_type'] ?? 'manual',
            ':source_id'         => isset($input['source_id']) && $input['source_id'] !== '' ? (int) $input['source_id'] : null,
            ':event_id'          => isset($input['event_id']) && $input['event_id'] !== '' ? (int) $input['event_id'] : null,
            ':member_id'         => isset($input['member_id']) && $input['member_id'] !== '' ? (int) $input['member_id'] : null,
            ':supplier_id'       => isset($input['supplier_id']) && $input['supplier_id'] !== '' ? (int) $input['supplier_id'] : null,
            ':purchase_order_id' => isset($input['purchase_order_id']) && $input['purchase_order_id'] !== '' ? (int) $input['purchase_order_id'] : null,
            ':description'       => $input['description'],
            ':recorded_by'       => (int) $user['id'],
        ];
        if ($hasAccountCol) {
            $params[':church_account_id'] = isset($input['church_account_id']) && $input['church_account_id'] !== '' ? (int) $input['church_account_id'] : null;
        }
        if ($hasApprovalCol) {
            $params[':approval_status'] = 'pending';
        }

        $stmt->execute($params);

        $id = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, (int) $user['id'], 'finance', 'create', 'finance_entries', $id, null, $input, 'Recorded finance entry');

        Response::json(['success' => true, 'message' => 'Finance entry created — awaiting approval', 'data' => ['id' => $id]], 201);
    }

    /* ───── Finance Dashboard Stats ───── */

    public function financeOverview(): void
    {
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            $month = date('Y-m');
        }

        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // Only count APPROVED entries in all financial totals.
        // NULL = legacy entries recorded before the approval workflow existed → treat as approved.
        // Only 'pending' and 'rejected' must be excluded from financial figures.
        $hasApprovalStatus = $this->columnExists('finance_entries', 'approval_status');
        $approvedFilter    = $hasApprovalStatus
            ? "AND (fe.approval_status = 'approved' OR fe.approval_status IS NULL)"
            : '';  // column doesn't exist yet: count everything

        // Total income this month (approved only)
        $incStmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fc.category_type='income' AND fe.entry_date BETWEEN :s AND :e {$approvedFilter}"
        );
        $incStmt->execute([':s' => $monthStart, ':e' => $monthEnd]);
        $monthIncome = (float) $incStmt->fetchColumn();

        // Total expense this month (approved only)
        $expStmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fc.category_type='expense' AND fe.entry_date BETWEEN :s AND :e {$approvedFilter}"
        );
        $expStmt->execute([':s' => $monthStart, ':e' => $monthEnd]);
        $monthExpense = (float) $expStmt->fetchColumn();

        // All-time totals (approved only)
        $allIncome = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fc.category_type='income' {$approvedFilter}"
        )->fetchColumn();
        $allExpense = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fc.category_type='expense' {$approvedFilter}"
        )->fetchColumn();

        // Pending pledges
        $pendingPledges = 0;
        if ($this->columnExists('pledges', 'id')) {
            $pledgeStmt = $this->pdo->query(
                "SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM pledges WHERE status IN ('active','overdue')"
            );
            $pendingPledges = (float) $pledgeStmt->fetchColumn();
        }

        // Category breakdown this month (approved only)
        $catStmt = $this->pdo->prepare(
            "SELECT fc.name, fc.category_type, COALESCE(SUM(fe.amount),0) AS total
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fe.entry_date BETWEEN :s AND :e {$approvedFilter}
             GROUP BY fc.id, fc.name, fc.category_type
             ORDER BY total DESC"
        );
        $catStmt->execute([':s' => $monthStart, ':e' => $monthEnd]);
        $categoryBreakdown = $catStmt->fetchAll();

        // Monthly trend — last 6 months (approved only)
        $trendStmt = $this->pdo->query(
            "SELECT DATE_FORMAT(fe.entry_date, '%Y-%m') AS month,
                    fc.category_type,
                    COALESCE(SUM(fe.amount), 0) AS total
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fe.entry_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
             {$approvedFilter}
             GROUP BY month, fc.category_type
             ORDER BY month ASC"
        );
        $trendRows = $trendStmt->fetchAll();
        $trend = [];
        foreach ($trendRows as $r) {
            $trend[$r['month']][$r['category_type']] = (float) $r['total'];
        }

        // Pending approvals count
        $pendingApprovals = 0;
        if ($hasApprovalStatus) {
            $pendingApprovals = (int) $this->pdo->query(
                "SELECT COUNT(*) FROM finance_entries WHERE approval_status = 'pending'"
            )->fetchColumn();
        }
        $pendingBudgets = 0;
        if ($this->columnExists('department_budgets', 'id')) {
            $pendingBudgets = (int) $this->pdo->query(
                "SELECT COUNT(*) FROM department_budgets WHERE status = 'submitted'"
            )->fetchColumn();
        }

        // Recent entries — show approved + pending so user can see what needs action
        // Rejected entries excluded from this view (audit trail available in reports)
        $approvalStatusSelect  = $hasApprovalStatus ? 'fe.approval_status,' : "'approved' AS approval_status,";
        $recentExcludeRejected = $hasApprovalStatus ? "AND (fe.approval_status != 'rejected' OR fe.approval_status IS NULL)" : '';
        $recentStmt = $this->pdo->query(
            "SELECT fe.id, fe.entry_no, fe.entry_date, fc.name AS category_name, fc.category_type,
                    fe.amount, fe.payment_method, fe.description, {$approvalStatusSelect}
                    m.first_name, m.last_name
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             LEFT JOIN members m ON m.id=fe.member_id
             WHERE 1=1 {$recentExcludeRejected}
             ORDER BY fe.entry_date DESC, fe.id DESC LIMIT 10"
        );

        // Church account balances
        $accounts = [];
        if ($this->columnExists('church_accounts', 'id')) {
            $hasAccountCol = $this->columnExists('finance_entries', 'church_account_id');
            if ($hasAccountCol) {
                $acctStmt = $this->pdo->query(
                    "SELECT ca.id, ca.account_name, ca.account_type, ca.bank_name, ca.opening_balance,
                            COALESCE(SUM(CASE WHEN fc.category_type='income' AND (fe.approval_status='approved' OR fe.approval_status IS NULL) THEN fe.amount ELSE 0 END), 0) AS total_in,
                            COALESCE(SUM(CASE WHEN fc.category_type='expense' AND (fe.approval_status='approved' OR fe.approval_status IS NULL) THEN fe.amount ELSE 0 END), 0) AS total_out
                     FROM church_accounts ca
                     LEFT JOIN finance_entries fe ON fe.church_account_id = ca.id
                     LEFT JOIN finance_categories fc ON fc.id = fe.category_id
                     WHERE ca.is_active = 1
                     GROUP BY ca.id
                     ORDER BY ca.account_type, ca.account_name"
                );
                foreach ($acctStmt->fetchAll() as $a) {
                    $a['balance'] = (float) $a['opening_balance'] + (float) $a['total_in'] - (float) $a['total_out'];
                    $a['total_in'] = (float) $a['total_in'];
                    $a['total_out'] = (float) $a['total_out'];
                    $accounts[] = $a;
                }
            } else {
                $acctStmt = $this->pdo->query("SELECT id, account_name, account_type, bank_name, opening_balance FROM church_accounts WHERE is_active = 1 ORDER BY account_type, account_name");
                foreach ($acctStmt->fetchAll() as $a) {
                    $a['balance'] = (float) $a['opening_balance'];
                    $a['total_in'] = 0;
                    $a['total_out'] = 0;
                    $accounts[] = $a;
                }
            }
        }

        Response::json([
            'success' => true,
            'data' => [
                'month' => $month,
                'month_income' => $monthIncome,
                'month_expense' => $monthExpense,
                'month_balance' => $monthIncome - $monthExpense,
                'all_time_income' => $allIncome,
                'all_time_expense' => $allExpense,
                'all_time_balance' => $allIncome - $allExpense,
                'pending_pledges' => $pendingPledges,
                'pending_approvals' => $pendingApprovals,
                'pending_budgets' => $pendingBudgets,
                'category_breakdown' => $categoryBreakdown,
                'trend' => $trend,
                'recent_entries' => $recentStmt->fetchAll(),
                'accounts' => $accounts,
            ],
        ]);
    }

    /* ───── Church Accounts (Bank/Cash/Mobile) ───── */

    public function listChurchAccounts(): void
    {
        if (!$this->columnExists('church_accounts', 'id')) {
            Response::json(['success' => true, 'data' => []]);
            return;
        }
        $rows = $this->pdo->query("SELECT * FROM church_accounts WHERE is_active = 1 ORDER BY account_type, account_name")->fetchAll();
        Response::json(['success' => true, 'data' => $rows]);
    }

    public function createChurchAccount(array $input): void
    {
        $name = trim((string) ($input['account_name'] ?? ''));
        if ($name === '') {
            Response::json(['success' => false, 'message' => 'Account name is required'], 422);
            return;
        }
        $stmt = $this->pdo->prepare(
            "INSERT INTO church_accounts (account_name, account_type, bank_name, account_number, opening_balance, description)
             VALUES (:name, :type, :bank, :acct_no, :opening, :desc)"
        );
        $stmt->execute([
            ':name' => $name,
            ':type' => $input['account_type'] ?? 'bank',
            ':bank' => trim((string) ($input['bank_name'] ?? '')) ?: null,
            ':acct_no' => trim((string) ($input['account_number'] ?? '')) ?: null,
            ':opening' => (float) ($input['opening_balance'] ?? 0),
            ':desc' => trim((string) ($input['description'] ?? '')) ?: null,
        ]);
        Response::json(['success' => true, 'message' => 'Account created', 'data' => ['id' => (int) $this->pdo->lastInsertId()]], 201);
    }

    public function updateChurchAccount(int $id, array $input): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE church_accounts SET account_name=:name, account_type=:type, bank_name=:bank, account_number=:acct_no, opening_balance=:opening, description=:desc WHERE id=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => trim((string) ($input['account_name'] ?? '')),
            ':type' => $input['account_type'] ?? 'bank',
            ':bank' => trim((string) ($input['bank_name'] ?? '')) ?: null,
            ':acct_no' => trim((string) ($input['account_number'] ?? '')) ?: null,
            ':opening' => (float) ($input['opening_balance'] ?? 0),
            ':desc' => trim((string) ($input['description'] ?? '')) ?: null,
        ]);
        Response::json(['success' => true, 'message' => 'Account updated']);
    }

    public function financeEntries(): void
    {
        $type = trim((string) ($_GET['type'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo = trim((string) ($_GET['date_to'] ?? ''));
        $search = trim((string) ($_GET['search'] ?? ''));
        $approval = trim((string) ($_GET['approval'] ?? ''));

        $hasApprovalStatus  = $this->columnExists('finance_entries', 'approval_status');
        $hasRejectionCount  = $this->columnExists('finance_entries', 'rejection_count');
        $hasRejectionReason = $this->columnExists('finance_entries', 'rejection_reason');
        $hasApprovalCols    = $hasApprovalStatus; // used for WHERE filter below
        $approvalSelect = ($hasApprovalStatus ? 'fe.approval_status' : "'approved' AS approval_status")
                        . ', '
                        . ($hasRejectionCount ? 'fe.rejection_count' : '0 AS rejection_count')
                        . ', '
                        . ($hasRejectionReason ? 'fe.rejection_reason' : 'NULL AS rejection_reason');

        $sql = "SELECT fe.id, fe.entry_no, fe.entry_date, fe.event_id, fe.source_id, fc.name AS category_name, fc.category_type,
                       fe.amount, fe.payment_method, fe.source_type, fe.description, {$approvalSelect},
                       fe.reference_no, m.first_name, m.last_name, m.member_code,
                       u.full_name AS recorded_by_name, a.full_name AS approved_by_name, fe.approved_at
                FROM finance_entries fe
                INNER JOIN finance_categories fc ON fc.id=fe.category_id
                LEFT JOIN members m ON m.id=fe.member_id
                LEFT JOIN users u ON u.id=fe.recorded_by
                LEFT JOIN users a ON a.id=fe.approved_by
                WHERE 1=1";
        $params = [];

        if ($type !== '' && in_array($type, ['income', 'expense'], true)) {
            $sql .= ' AND fc.category_type = :type';
            $params[':type'] = $type;
        }
        if ($category !== '' && ctype_digit($category)) {
            $sql .= ' AND fe.category_id = :cat';
            $params[':cat'] = (int) $category;
        }
        if ($dateFrom !== '' && strtotime($dateFrom) !== false) {
            $sql .= ' AND fe.entry_date >= :df';
            $params[':df'] = $dateFrom;
        }
        if ($dateTo !== '' && strtotime($dateTo) !== false) {
            $sql .= ' AND fe.entry_date <= :dt';
            $params[':dt'] = $dateTo;
        }
        if ($search !== '') {
            $sql .= ' AND (fe.description LIKE :s1 OR fe.entry_no LIKE :s2 OR m.first_name LIKE :s3 OR m.last_name LIKE :s4)';
            $like = '%' . $search . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
            $params[':s4'] = $like;
        }
        if ($approval !== '' && in_array($approval, ['pending', 'approved', 'rejected'], true) && $hasApprovalCols) {
            // Explicit filter requested (e.g. Approvals tab asking for ?approval=pending)
            $sql .= ' AND fe.approval_status = :appr';
            $params[':appr'] = $approval;
        } elseif ($approval === '' && $hasApprovalCols) {
            // No explicit filter: hide only 'rejected'. NULL = legacy approved entry, keep it.
            $sql .= " AND (fe.approval_status != 'rejected' OR fe.approval_status IS NULL)";
        }
        // When approval column doesn't exist (pre-migration), show all entries

        $sql .= ' ORDER BY fe.entry_date DESC, fe.id DESC LIMIT 500';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    public function approveFinanceEntry(int $id, array $input): void
    {
        $decision = trim((string) ($input['decision'] ?? ''));
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            Response::json(['success' => false, 'message' => 'Decision must be approved or rejected'], 422);
            return;
        }

        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
            return;
        }
        if (!Auth::can('finance.approve')) {
            Response::json(['success' => false, 'message' => 'No permission to approve finance entries'], 403);
            return;
        }

        // Enforce workflow level
        $approvalLevel = 1;
        $isLastLevel = true;
        $nextLevel = $this->getNextApprovalLevel('finance_entry', $id);
        if ($nextLevel !== null) {
            if (isset($nextLevel['done'])) {
                Response::json(['success' => false, 'message' => 'Entry already fully approved at all levels'], 422);
                return;
            }
            $approvalLevel = (int) $nextLevel['level_no'];
            $isLastLevel   = !empty($nextLevel['is_final']);
            $isAdmin       = strtolower($user['role'] ?? '') === 'admin';
            if (!$isAdmin && (int) ($user['role_id'] ?? 0) !== (int) $nextLevel['role_id']) {
                $rStmt = $this->pdo->prepare('SELECT name FROM roles WHERE id = :rid');
                $rStmt->execute([':rid' => $nextLevel['role_id']]);
                $roleName = $rStmt->fetchColumn() ?: ('Role #' . $nextLevel['role_id']);
                Response::json(['success' => false, 'message' => "Level {$approvalLevel} approval requires {$roleName}"], 403);
                return;
            }
        }

        $hasApprovalCols    = $this->columnExists('finance_entries', 'approval_status');
        $hasRejectionCount  = $this->columnExists('finance_entries', 'rejection_count');
        $hasRejectionReason = $this->columnExists('finance_entries', 'rejection_reason');
        $hasBudgetCols      = $this->columnExists('events', 'budget_status');

        // Get the entry first to check source
        $approvalColsSelect = ($hasApprovalCols ? ', approval_status' : '')
                            . ($hasRejectionCount ? ', rejection_count' : '');
        $entryStmt = $this->pdo->prepare(
            "SELECT id, entry_no, source_type, event_id, amount{$approvalColsSelect}
             FROM finance_entries WHERE id = :id LIMIT 1"
        );
        $entryStmt->execute([':id' => $id]);
        $entry = $entryStmt->fetch();
        if (!$entry) {
            Response::json(['success' => false, 'message' => 'Finance entry not found'], 404);
            return;
        }

        $rejectionNotes = trim((string) ($input['notes'] ?? ''));

        $this->pdo->beginTransaction();
        try {
            // Update finance entry status
            if ($hasApprovalCols) {
                $effectiveStatus = $decision;
                if ($decision === 'approved' && !$isLastLevel) {
                    $effectiveStatus = 'pending'; // Intermediate level — keep pending
                }
                $setCols = 'approval_status = :status, approved_by = :uid, approved_at = NOW()';
                $params  = [':status' => $effectiveStatus, ':uid' => (int) $user['id'], ':id' => $id];

                if ($decision === 'rejected' && $hasRejectionCount) {
                    $rejCount = ((int) ($entry['rejection_count'] ?? 0)) + 1;
                    $setCols .= ', rejection_count = :rc';
                    $params[':rc'] = $rejCount;
                }
                if ($decision === 'rejected' && $hasRejectionReason && $rejectionNotes !== '') {
                    $setCols .= ', rejection_reason = :rr';
                    $params[':rr'] = $rejectionNotes;
                }
                if ($decision === 'approved' && $hasRejectionReason) {
                    $setCols .= ', rejection_reason = NULL';
                }

                $stmt = $this->pdo->prepare("UPDATE finance_entries SET {$setCols} WHERE id = :id");
                $stmt->execute($params);
            } else {
                $stmt = $this->pdo->prepare(
                    'UPDATE finance_entries SET approved_by = :uid, approved_at = NOW() WHERE id = :id'
                );
                $stmt->execute([':uid' => (int) $user['id'], ':id' => $id]);
            }

            // Cascade to event if this entry came from an event budget (only on final approval/rejection)
            $eventId = (int) ($entry['event_id'] ?? 0);
            if ($entry['source_type'] === 'event' && $eventId > 0 && $hasBudgetCols && ($decision === 'rejected' || $isLastLevel)) {
                $budgetStatus = $decision === 'approved' ? 'approved' : 'rejected';
                $updateEvent = $this->pdo->prepare(
                    'UPDATE `events` SET budget_status = :bs, budget_approved_by = :uid, budget_approved_at = NOW() WHERE id = :eid'
                );
                $updateEvent->execute([':bs' => $budgetStatus, ':uid' => (int) $user['id'], ':eid' => $eventId]);
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
            return;
        }

        // Log approval to approval_logs table
        $this->logApproval('finance_entry', $id, $decision, (int) $user['id'], $rejectionNotes ?: null, $approvalLevel);

        Audit::log($this->pdo, (int) $user['id'], 'finance', $decision, 'finance_entries', $id, null, [
            'decision' => $decision,
            'event_id' => $eventId ?? null,
            'amount' => $entry['amount'] ?? 0,
            'notes' => $rejectionNotes,
            'level' => $approvalLevel,
        ], "Finance entry $decision (L{$approvalLevel})");

        $levelMsg = $nextLevel !== null && !$isLastLevel ? " (Level {$approvalLevel}/{$nextLevel['total']})" : '';
        Response::json(['success' => true, 'message' => "Entry {$entry['entry_no']} $decision successfully{$levelMsg}"]);
    }

    /* ───── Pledges (Ahadi) ───── */

    public function listPledges(): void
    {
        $status = trim((string) ($_GET['status'] ?? ''));
        $sql = "SELECT p.id, p.pledge_no, p.campaign, p.description, p.total_amount, p.paid_amount,
                       (p.total_amount - p.paid_amount) AS balance,
                       p.pledge_date, p.due_date, p.status,
                       m.first_name, m.last_name, m.member_code, m.phone
                FROM pledges p
                INNER JOIN members m ON m.id = p.member_id
                WHERE 1=1";
        $params = [];
        if ($status !== '' && in_array($status, ['active', 'completed', 'cancelled', 'overdue'], true)) {
            $sql .= ' AND p.status = :st';
            $params[':st'] = $status;
        }
        $sql .= ' ORDER BY p.pledge_date DESC LIMIT 500';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    public function createPledge(array $input): void
    {
        $required = ['member_id', 'total_amount', 'pledge_date'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                Response::json(['success' => false, 'message' => "$f is required"], 422);
            }
        }
        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $pledgeNo = trim((string) ($input['pledge_no'] ?? ''));
        if ($pledgeNo === '') {
            $seq = (int) $this->pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM pledges")->fetchColumn();
            $pledgeNo = 'PLG-' . date('Y') . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO pledges (pledge_no, member_id, campaign, description, total_amount, pledge_date, due_date, status, created_by)
             VALUES (:pno, :mid, :camp, :desc, :amt, :pdate, :due, :st, :uid)'
        );
        $stmt->execute([
            ':pno' => $pledgeNo,
            ':mid' => (int) $input['member_id'],
            ':camp' => trim((string) ($input['campaign'] ?? '')),
            ':desc' => trim((string) ($input['description'] ?? '')),
            ':amt' => (float) $input['total_amount'],
            ':pdate' => $input['pledge_date'],
            ':due' => isset($input['due_date']) && $input['due_date'] !== '' ? $input['due_date'] : null,
            ':st' => 'active',
            ':uid' => (int) $user['id'],
        ]);
        $id = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, (int) $user['id'], 'finance', 'create_pledge', 'pledges', $id, null, $input, 'Created pledge');
        Response::json(['success' => true, 'message' => 'Pledge created', 'data' => ['id' => $id, 'pledge_no' => $pledgeNo]], 201);
    }

    /** List campaigns */
    public function listCampaigns(): void
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id, title, description, start_date, end_date, status FROM campaigns ORDER BY start_date DESC');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::json(['success' => true, 'data' => $rows]);
        } catch (	hrowable $e) {
            Response::json(['success' => false, 'message' => 'Failed to fetch campaigns: ' . $e->getMessage()], 500);
        }
    }

    /** Create campaign */
    public function createCampaign(array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); }

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') { Response::json(['success' => false, 'message' => 'Title is required'], 422); }

        $desc = trim((string) ($input['description'] ?? ''));
        $start = trim((string) ($input['start_date'] ?? '')) ?: null;
        $end   = trim((string) ($input['end_date'] ?? '')) ?: null;

        try {
            $stmt = $this->pdo->prepare('INSERT INTO campaigns (title, description, start_date, end_date, created_by, created_at) VALUES (:title, :desc, :start, :end, :uid, NOW())');
            $stmt->execute([':title' => $title, ':desc' => $desc, ':start' => $start, ':end' => $end, ':uid' => (int) $user['id']]);
            $id = (int) $this->pdo->lastInsertId();
            Audit::log($this->pdo, (int) $user['id'], 'finance', 'create_campaign', 'campaigns', $id, null, ['title' => $title], 'Created campaign');
            Response::json(['success' => true, 'message' => 'Campaign created', 'data' => ['id' => $id]], 201);
        } catch (	hrowable $e) {
            Response::json(['success' => false, 'message' => 'Failed to create campaign: ' . $e->getMessage()], 500);
        }
    }

    /** Import pledges from CSV file */
    public function importPledges(array $input, array $files): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); }

        if (!isset($files['file']) || $files['file']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'File upload required'], 422);
        }

        $file = $files['file'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext !== 'csv') { Response::json(['success' => false, 'message' => 'Only CSV files are supported'], 422); }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) { Response::json(['success' => false, 'message' => 'Unable to read uploaded file'], 500); }

        $headers = null;
        $rowNum = 0;
        $imported = 0;
        $errors = [];

        $this->pdo->beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if ($rowNum === 1) {
                    $headers = array_map('trim', array_map('strtolower', $row));
                    continue;
                }

                $rowAssoc = [];
                foreach ($headers as $i => $h) { $rowAssoc[$h] = $row[$i] ?? ''; }

                // Expect either member_code or first_name+last_name
                $memberId = null;
                if (!empty($rowAssoc['member_code'])) {
                    $stmt = $this->pdo->prepare('SELECT id FROM members WHERE member_code = ? LIMIT 1');
                    $stmt->execute([trim($rowAssoc['member_code'])]);
                    $m = $stmt->fetch();
                    if ($m) $memberId = (int) $m['id'];
                } else {
                    $fn = trim($rowAssoc['first_name'] ?? ''); $ln = trim($rowAssoc['last_name'] ?? '');
                    if ($fn !== '' && $ln !== '') {
                        $stmt = $this->pdo->prepare('SELECT id FROM members WHERE first_name = ? AND last_name = ? LIMIT 1');
                        $stmt->execute([$fn, $ln]);
                        $m = $stmt->fetch();
                        if ($m) $memberId = (int) $m['id'];
                    }
                }

                if (!$memberId) { $errors[] = "Row {$rowNum}: member not found"; continue; }

                $amount = (float) ($rowAssoc['amount'] ?? 0);
                if ($amount <= 0) { $errors[] = "Row {$rowNum}: invalid amount"; continue; }

                $pledgeDate = trim($rowAssoc['pledge_date'] ?? '') ?: date('Y-m-d');
                $dueDate = trim($rowAssoc['due_date'] ?? '') ?: null;
                $campaign = trim($rowAssoc['campaign'] ?? '');
                $description = trim($rowAssoc['description'] ?? '');

                $pledgeNo = 'PLG-' . date('Y') . '-' . str_pad((string) ((int) $this->pdo->query('SELECT COALESCE(MAX(id),0)+1 FROM pledges')->fetchColumn()), 3, '0', STR_PAD_LEFT);

                $ins = $this->pdo->prepare('INSERT INTO pledges (pledge_no, member_id, campaign, description, total_amount, pledge_date, due_date, status, created_by) VALUES (:pno, :mid, :camp, :desc, :amt, :pdate, :due, :st, :uid)');
                $ins->execute([
                    ':pno' => $pledgeNo,
                    ':mid' => $memberId,
                    ':camp' => $campaign,
                    ':desc' => $description,
                    ':amt' => $amount,
                    ':pdate' => $pledgeDate,
                    ':due' => $dueDate !== '' ? $dueDate : null,
                    ':st' => 'active',
                    ':uid' => (int) $user['id'],
                ]);
                $imported++;
            }

            $this->pdo->commit();
            fclose($handle);
            Response::json(['success' => true, 'message' => "Import completed: {$imported} pledges imported", 'data' => ['imported' => $imported, 'errors' => $errors]]);
        } catch (	hrowable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            Response::json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    public function pledgeStats(): void
    {
        $row = $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(status='active') AS active,
                    SUM(status='completed') AS completed,
                    SUM(status='overdue') AS overdue,
                    COALESCE(SUM(total_amount),0) AS total_pledged,
                    COALESCE(SUM(paid_amount),0) AS total_paid,
                    COALESCE(SUM(total_amount - paid_amount),0) AS total_balance
             FROM pledges"
        )->fetch();
        Response::json(['success' => true, 'data' => $row]);
    }

    /* ───── Departments ───── */

    public function listDepartments(): void
    {
        try {
            if (!$this->columnExists('departments', 'id')) {
                Response::json(['success' => true, 'data' => []]);
                return;
            }
            
            // Check if head_email column exists
            $hasHeadEmail = $this->columnExists('departments', 'head_email');
            
            $onlyActive = ($_GET['active'] ?? '') === '1';
            $headEmailCol = $hasHeadEmail ? ', d.head_email' : '';
            $sql = "SELECT d.id, d.name, d.description, d.is_active, d.head_name
                           {$headEmailCol}
                    FROM departments d
                    " . ($onlyActive ? "WHERE d.is_active = 1" : "") . "
                    ORDER BY d.name ASC";
            $rows = $this->pdo->query($sql)->fetchAll();
            Response::json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function createDepartment(array $input): void
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            Response::json(['success' => false, 'message' => 'Department name is required'], 422);
            return;
        }
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('departments.create')) {
            Response::json(['success' => false, 'message' => 'No permission to create departments'], 403); return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO departments (name, description, head_name, is_active)
             VALUES (:name, :desc, :head_name, 1)'
        );
        $stmt->execute([
            ':name' => $name,
            ':desc' => trim((string) ($input['description'] ?? '')),
            ':head_name' => trim((string) ($input['head_name'] ?? '')) ?: null,
        ]);
        $id = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, (int) $user['id'], 'settings', 'create_department', 'departments', $id, null, $input, 'Created department');
        Response::json(['success' => true, 'message' => 'Department created', 'data' => ['id' => $id]], 201);
    }

    public function updateDepartment(int $id, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('departments.edit')) {
            Response::json(['success' => false, 'message' => 'No permission to edit departments'], 403); return;
        }

        $fields = [];
        $params = [':id' => $id];
        if (isset($input['name'])        && trim($input['name']) !== '') { $fields[] = 'name = :name'; $params[':name'] = trim($input['name']); }
        if (array_key_exists('description', $input))                      { $fields[] = 'description = :desc'; $params[':desc'] = trim((string)$input['description']); }
        if (array_key_exists('head_name', $input))                        { $fields[] = 'head_name = :head_name'; $params[':head_name'] = trim((string)$input['head_name']) ?: null; }
        if (array_key_exists('is_active', $input))                        { $fields[] = 'is_active = :active'; $params[':active'] = (int)(bool)$input['is_active']; }

        if (empty($fields)) {
            Response::json(['success' => false, 'message' => 'Nothing to update'], 422);
            return;
        }
        $stmt = $this->pdo->prepare('UPDATE departments SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $stmt->execute($params);
        Audit::log($this->pdo, (int) $user['id'], 'settings', 'update_department', 'departments', $id, null, $input, 'Updated department');
        Response::json(['success' => true, 'message' => 'Department updated']);
    }

    public function deleteDepartment(int $id): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('departments.delete')) {
            Response::json(['success' => false, 'message' => 'No permission to delete departments'], 403); return;
        }
        // Soft-delete: just deactivate
        $stmt = $this->pdo->prepare('UPDATE departments SET is_active = 0 WHERE id = :id');
        $stmt->execute([':id' => $id]);
        Audit::log($this->pdo, (int) $user['id'], 'settings', 'deactivate_department', 'departments', $id, null, [], 'Deactivated department');
        Response::json(['success' => true, 'message' => 'Department deactivated']);
    }

    public function setDepartmentCredentials(int $id, array $input): void
    {
        try {
            $user = Auth::user();
            if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

            // Check if required columns exist (migrations must be run first)
            if (!$this->columnExists('departments', 'head_email')) {
                Response::json(['success' => false, 'message' => 'Database migration required. Please run: database/migrations/2026_04_09_001_modify_departments_for_separate_login.sql'], 400);
                return;
            }

            $email = trim((string) ($input['head_email'] ?? ''));
            $password = trim((string) ($input['head_password'] ?? ''));
            $passwordConfirm = trim((string) ($input['head_password_confirm'] ?? ''));

            // Validation - require both email and password
            if (empty($email)) {
                Response::json(['success' => false, 'message' => 'Email is required'], 422);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Response::json(['success' => false, 'message' => 'Invalid email format'], 422);
                return;
            }

            if (empty($password)) {
                Response::json(['success' => false, 'message' => 'Password is required'], 422);
                return;
            }

            if (strlen($password) < 6) {
                Response::json(['success' => false, 'message' => 'Password must be at least 6 characters'], 422);
                return;
            }

            if ($password !== $passwordConfirm) {
                Response::json(['success' => false, 'message' => 'Passwords do not match'], 422);
                return;
            }

            // Check email uniqueness (excluding current department)
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as cnt FROM departments WHERE head_email = :email AND id != :id');
            $stmt->execute([':email' => $email, ':id' => $id]);
            $row = $stmt->fetch();
            if ($row['cnt'] > 0) {
                Response::json(['success' => false, 'message' => 'Email is already in use by another department'], 422);
                return;
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update department with email and password
            $stmt = $this->pdo->prepare('UPDATE departments SET head_email = :email, head_password_hash = :password WHERE id = :id');
            $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword,
                ':id' => $id,
            ]);

            Audit::log($this->pdo, (int) $user['id'], 'settings', 'set_department_credentials', 'departments', $id, null,
                ['head_email' => $email], 'Set department head credentials');

            Response::json(['success' => true, 'message' => 'Department credentials saved successfully']);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /* ───── Admin: Department Overview / Detail ───── */

    public function departmentOverview(int $id): void
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id, name, description, head_name, head_email, is_active, created_at FROM departments WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $dept = $stmt->fetch();
            if (!$dept) { Response::json(['success' => false, 'message' => 'Department not found'], 404); return; }

            $memberCount = 0;
            $leaderCount = 0;
            $totalIncome = 0;
            $totalExpense = 0;
            $draftReports = 0;
            $submittedReports = 0;

            if ($this->columnExists('department_members', 'id')) {
                $r = $this->pdo->prepare('SELECT COUNT(*) FROM department_members WHERE department_id = :id');
                $r->execute([':id' => $id]);
                $memberCount = (int) $r->fetchColumn();
            }
            if ($this->columnExists('department_leaders', 'id')) {
                $r = $this->pdo->prepare('SELECT COUNT(*) FROM department_leaders WHERE department_id = :id AND is_active = 1');
                $r->execute([':id' => $id]);
                $leaderCount = (int) $r->fetchColumn();
            }
            if ($this->columnExists('finance_entries', 'department_id')) {
                $r = $this->pdo->prepare(
                    'SELECT SUM(CASE WHEN fc.category_type="income" THEN fe.amount ELSE 0 END) as inc,
                            SUM(CASE WHEN fc.category_type="expense" THEN fe.amount ELSE 0 END) as exp
                     FROM finance_entries fe
                     LEFT JOIN finance_categories fc ON fc.id = fe.category_id
                     WHERE fe.department_id = :id'
                );
                $r->execute([':id' => $id]);
                $fin = $r->fetch();
                $totalIncome  = (float) ($fin['inc'] ?? 0);
                $totalExpense = (float) ($fin['exp'] ?? 0);
            }
            if ($this->columnExists('department_reports', 'id')) {
                $r = $this->pdo->prepare("SELECT status, COUNT(*) as cnt FROM department_reports WHERE department_id = :id GROUP BY status");
                $r->execute([':id' => $id]);
                foreach ($r->fetchAll() as $row) {
                    if ($row['status'] === 'draft')     $draftReports    = (int) $row['cnt'];
                    if ($row['status'] === 'submitted') $submittedReports = (int) $row['cnt'];
                }
            }

            Response::json(['success' => true, 'data' => [
                'department'       => $dept,
                'member_count'     => $memberCount,
                'leader_count'     => $leaderCount,
                'total_income'     => $totalIncome,
                'total_expense'    => $totalExpense,
                'balance'          => $totalIncome - $totalExpense,
                'draft_reports'    => $draftReports,
                'submitted_reports'=> $submittedReports,
            ]]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function departmentMembers(int $id): void
    {
        try {
            if (!$this->columnExists('department_members', 'id')) {
                Response::json(['success' => true, 'data' => []]); return;
            }
            $stmt = $this->pdo->prepare(
                'SELECT dm.id, m.id AS member_id, m.first_name, m.last_name, m.phone, m.email,
                        dm.notes AS role_note, dm.assigned_date
                 FROM department_members dm
                 JOIN members m ON m.id = dm.member_id
                 WHERE dm.department_id = :id
                 ORDER BY m.first_name, m.last_name'
            );
            $stmt->execute([':id' => $id]);
            Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function departmentLeaders(int $id): void
    {
        try {
            if (!$this->columnExists('department_leaders', 'id')) {
                Response::json(['success' => true, 'data' => []]); return;
            }
            $stmt = $this->pdo->prepare(
                'SELECT id, leader_name, leader_type, email, phone, is_active, created_at
                 FROM department_leaders WHERE department_id = :id ORDER BY leader_type, leader_name'
            );
            $stmt->execute([':id' => $id]);
            Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function departmentFinance(int $id): void
    {
        try {
            if (!$this->columnExists('finance_entries', 'department_id')) {
                Response::json(['success' => true, 'data' => [], 'summary' => ['income' => 0, 'expense' => 0, 'balance' => 0]]); return;
            }
            $limit  = min((int) ($_GET['limit'] ?? 50), 200);
            $offset = max((int) ($_GET['offset'] ?? 0), 0);
            $type   = in_array($_GET['type'] ?? '', ['income','expense'], true) ? $_GET['type'] : null;

            $where = 'WHERE department_id = :id AND deleted_at IS NULL';
            $params = [':id' => $id];
            if ($type) { $where .= ' AND type = :type'; $params[':type'] = $type; }

            $stmt = $this->pdo->prepare(
                "SELECT id, type, category, amount, description, created_at
                 FROM finance_entries $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            $s = $this->pdo->prepare(
                'SELECT SUM(CASE WHEN type="income" THEN amount ELSE 0 END) as inc,
                        SUM(CASE WHEN type="expense" THEN amount ELSE 0 END) as exp
                 FROM finance_entries WHERE department_id = :id AND deleted_at IS NULL'
            );
            $s->execute([':id' => $id]);
            $fin = $s->fetch();
            $inc = (float)($fin['inc'] ?? 0);
            $exp = (float)($fin['exp'] ?? 0);

            Response::json(['success' => true, 'data' => $rows,
                'summary' => ['income' => $inc, 'expense' => $exp, 'balance' => $inc - $exp]]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function departmentReports(int $id): void
    {
        try {
            if (!$this->columnExists('department_reports', 'id')) {
                Response::json(['success' => true, 'data' => []]); return;
            }
            $status = trim($_GET['status'] ?? '');
            $where  = 'WHERE dr.department_id = :id';
            $params = [':id' => $id];
            if (in_array($status, ['draft','submitted','approved','rejected'], true)) {
                $where .= ' AND dr.status = :status'; $params[':status'] = $status;
            }
            $stmt = $this->pdo->prepare(
                "SELECT dr.id, dr.title, dr.category, dr.status, dr.report_date,
                        dr.submitted_at, dr.reviewed_at, dr.review_notes, dr.description,
                        u.full_name AS reviewed_by_name
                 FROM department_reports dr
                 LEFT JOIN users u ON u.id = dr.reviewed_by
                 $where ORDER BY dr.report_date DESC"
            );
            $stmt->execute($params);
            Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get weekly financial aggregation for department reports
     * Calculates income and expenses for the week containing the given date
     */
    public function departmentWeeklyFinanceReport(int $id): void
    {
        try {
            if (!$this->columnExists('finance_entries', 'department_id')) {
                Response::json(['success' => true, 'data' => [
                    'week_start' => null,
                    'week_end' => null,
                    'weekly_income' => 0,
                    'weekly_expense' => 0,
                    'weekly_balance' => 0,
                ]]);
                return;
            }

            // Calculate current week (Monday to Friday/Sunday based on today)
            $today = date('Y-m-d');
            $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($today)));
            $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($today)));

            // Get weekly aggregates
            $stmt = $this->pdo->prepare(
                'SELECT 
                    SUM(CASE WHEN type="income" THEN amount ELSE 0 END) as weekly_income,
                    SUM(CASE WHEN type="expense" THEN amount ELSE 0 END) as weekly_expense
                 FROM finance_entries
                 WHERE department_id = :id 
                   AND DATE(created_at) BETWEEN :week_start AND :week_end
                   AND deleted_at IS NULL'
            );
            $stmt->execute([
                ':id' => $id,
                ':week_start' => $weekStart,
                ':week_end' => $weekEnd
            ]);
            $result = $stmt->fetch() ?: [];
            $income = (float)($result['weekly_income'] ?? 0);
            $expense = (float)($result['weekly_expense'] ?? 0);

            Response::json([
                'success' => true,
                'data' => [
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'weekly_income' => $income,
                    'weekly_expense' => $expense,
                    'weekly_balance' => $income - $expense,
                ]
            ]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reviewDepartmentReport(int $deptId, int $reportId, array $input): void
    {
        try {
            $user = Auth::user();
            if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

            $action = trim((string)($input['action'] ?? ''));
            if (!in_array($action, ['approve','reject'], true)) {
                Response::json(['success' => false, 'message' => 'action must be approve or reject'], 422); return;
            }
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $notes  = trim((string)($input['notes'] ?? ''));

            if (!$this->columnExists('department_reports', 'id')) {
                Response::json(['success' => false, 'message' => 'Table not found'], 500); return;
            }

            $stmt = $this->pdo->prepare(
                'UPDATE department_reports SET status = :status, review_notes = :notes,
                 reviewed_by = :uid, reviewed_at = NOW()
                 WHERE id = :id AND department_id = :did'
            );
            $stmt->execute([':status' => $status, ':notes' => $notes,
                ':uid' => $user['id'], ':id' => $reportId, ':did' => $deptId]);

            Audit::log($this->pdo, (int)$user['id'], 'departments', $action . '_report', 'department_reports', $reportId,
                null, $input, ucfirst($action) . 'd department report');

            Response::json(['success' => true, 'message' => 'Report ' . $status]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ───── Department Contributions (admin view) ───── */

    public function departmentContributions(int $id): void
    {
        try {
            $user = Auth::user();
            if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

            if (!$this->columnExists('department_contributions', 'id')) {
                Response::json(['success' => true, 'data' => [], 'summary' => []]);
                return;
            }

            $method = trim((string)($_GET['method'] ?? ''));
            $from   = trim((string)($_GET['from']   ?? ''));
            $to     = trim((string)($_GET['to']     ?? ''));

            $where  = ['c.department_id = :id'];
            $params = [':id' => $id];

            if ($method && in_array($method, ['cash','mpesa','cheque','bank_transfer','other'], true)) {
                $where[] = 'c.payment_method = :method';
                $params[':method'] = $method;
            }
            if ($from) { $where[] = 'c.contribution_date >= :from'; $params[':from'] = $from; }
            if ($to)   { $where[] = 'c.contribution_date <= :to';   $params[':to']   = $to;   }

            $sql = 'SELECT c.id, c.member_id, c.contributor_name, c.amount, c.payment_method,
                           c.purpose, c.contribution_date, c.recorded_by,
                           m.first_name, m.last_name
                    FROM department_contributions c
                    LEFT JOIN members m ON c.member_id = m.id
                    WHERE ' . implode(' AND ', $where) . '
                    ORDER BY c.contribution_date DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Build display name
            foreach ($rows as &$r) {
                $r['display_name'] = $r['member_id']
                    ? trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))
                    : ($r['contributor_name'] ?? 'Unknown');
                $r['is_member'] = !empty($r['member_id']);
            }
            unset($r);

            // Summary
            $sumStmt = $this->pdo->prepare(
                'SELECT SUM(amount) AS total, COUNT(*) AS count,
                        SUM(CASE WHEN payment_method="cash" THEN amount ELSE 0 END) AS cash,
                        SUM(CASE WHEN payment_method="mpesa" THEN amount ELSE 0 END) AS mpesa
                 FROM department_contributions WHERE department_id = :id'
            );
            $sumStmt->execute([':id' => $id]);
            $summary = $sumStmt->fetch(\PDO::FETCH_ASSOC);

            Response::json(['success' => true, 'data' => $rows, 'summary' => $summary]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ───── Department Assets assigned (admin view) ───── */

    public function departmentAssets(int $id): void
    {
        try {
            $user = Auth::user();
            if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

            // Check column added by migration
            if (!$this->columnExists('asset_assignments', 'assigned_department_id')) {
                Response::json(['success' => true, 'data' => []]);
                return;
            }

            $stmt = $this->pdo->prepare(
                'SELECT a.id, a.asset_tag, a.name, a.category, a.condition_status,
                        a.current_location, a.notes,
                        aa.assigned_from, aa.notes AS assignment_notes
                 FROM asset_assignments aa
                 JOIN assets a ON aa.asset_id = a.id
                 WHERE aa.assigned_type = "department"
                   AND aa.assigned_department_id = :id
                   AND aa.assigned_to IS NULL
                   AND a.is_active = 1
                 ORDER BY a.category, a.name'
            );
            $stmt->execute([':id' => $id]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            Response::json(['success' => true, 'data' => $rows, 'total' => count($rows)]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ───── Department Budgets ───── */

    public function listBudgets(): void
    {
        // department_budgets table may not exist before migration
        if (!$this->columnExists('department_budgets', 'id')) {
            Response::json(['success' => true, 'data' => []]);
            return;
        }

        $month  = trim((string) ($_GET['month']  ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        // Compute spent_amount dynamically from approved finance_entries matched by category + month.
        // Falls back to the stored spent_amount when no category_id is linked on the budget.
        $hasApprovalCol = $this->columnExists('finance_entries', 'approval_status');
        $approvalWhere  = $hasApprovalCol ? "AND fe.approval_status = 'approved'" : '';

        // Check which new workflow columns exist (added via migration)
        $hasActual   = $this->columnExists('department_budgets', 'actual_amount');
        $hasEntryId  = $this->columnExists('department_budgets', 'finance_entry_id');
        $hasEventId  = $this->columnExists('department_budgets', 'event_id');
        $hasDesc     = $this->columnExists('department_budgets', 'description');
        $actualCols  = $hasActual
            ? 'db.actual_amount, db.actual_notes, db.closed_at, db.closed_by,'
            : '0 AS actual_amount, NULL AS actual_notes, NULL AS closed_at, NULL AS closed_by,';
        $entryCol    = $hasEntryId ? 'db.finance_entry_id,' : 'NULL AS finance_entry_id,';
        $eventCol    = $hasEventId ? 'db.event_id, ev.title AS event_title,' : 'NULL AS event_id, NULL AS event_title,';
        $descCol     = $hasDesc ? 'db.description,' : 'NULL AS description,';
        $eventJoin   = $hasEventId ? 'LEFT JOIN events ev ON ev.id = db.event_id' : '';

        // Compute total from budget_expenses table
        $hasBudgetExpenses = $this->tableExists('budget_expenses');
        $beJoin  = $hasBudgetExpenses ? 'LEFT JOIN (SELECT budget_id, SUM(amount) AS total_used, COUNT(*) AS expense_count FROM budget_expenses GROUP BY budget_id) be ON be.budget_id = db.id' : '';
        $beCols  = $hasBudgetExpenses ? 'COALESCE(be.total_used, 0) AS total_used, COALESCE(be.expense_count, 0) AS expense_count,' : '0 AS total_used, 0 AS expense_count,';

        // Check for reserved_amount column
        $hasReserved = $this->columnExists('department_budgets', 'reserved_amount');
        $reservedCol = $hasReserved ? 'db.reserved_amount,' : '0 AS reserved_amount,';

        $sql = "SELECT db.id, db.department, db.fiscal_month, db.planned_amount,
                       $reservedCol
                       $actualCols
                       $entryCol
                       $eventCol
                       $descCol
                       $beCols
                       COALESCE(exp_total.total, db.spent_amount) AS spent_amount,
                       db.status, db.notes, db.category_id,
                       fc.name AS category_name,
                       u.full_name AS submitted_by_name,
                       a.full_name AS approved_by_name,
                       db.approved_at, db.created_at
                FROM department_budgets db
                LEFT JOIN users u  ON u.id = db.submitted_by
                LEFT JOIN users a  ON a.id = db.approved_by
                LEFT JOIN finance_categories fc ON fc.id = db.category_id
                $eventJoin
                $beJoin
                LEFT JOIN (
                    SELECT fe.category_id,
                           DATE_FORMAT(fe.entry_date, '%Y-%m') AS month_key,
                           SUM(fe.amount) AS total
                    FROM finance_entries fe
                    WHERE fe.category_id IS NOT NULL $approvalWhere
                    GROUP BY fe.category_id, DATE_FORMAT(fe.entry_date, '%Y-%m')
                ) exp_total ON exp_total.category_id = db.category_id
                          AND exp_total.month_key   = db.fiscal_month
                          AND db.category_id IS NOT NULL
                WHERE 1=1";
        $params = [];
        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $sql .= ' AND db.fiscal_month = :m';
            $params[':m'] = $month;
        }
        $allowedStatuses = ['draft', 'submitted', 'approved', 'rejected', 'expenses_added', 'closed'];
        if ($status !== '' && in_array($status, $allowedStatuses, true)) {
            $sql .= ' AND db.status = :st';
            $params[':st'] = $status;
        }
        $sql .= ' ORDER BY db.fiscal_month DESC, db.department ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    public function createBudget(array $input): void
    {
        $required = ['department', 'fiscal_month', 'planned_amount'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                Response::json(['success' => false, 'message' => "$f is required"], 422);
            }
        }
        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $hasEventId = $this->columnExists('department_budgets', 'event_id');
        $hasDesc    = $this->columnExists('department_budgets', 'description');

        $extraCols = '';
        $extraVals = '';
        $extraParams = [];

        if ($hasEventId && !empty($input['event_id'])) {
            $extraCols .= ', event_id';
            $extraVals .= ', :evid';
            $extraParams[':evid'] = (int) $input['event_id'];
        }
        if ($hasDesc) {
            $extraCols .= ', description';
            $extraVals .= ', :desc';
            $extraParams[':desc'] = trim((string) ($input['description'] ?? ''));
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO department_budgets (department, category_id, fiscal_month, planned_amount, status, submitted_by, notes $extraCols)
             VALUES (:dept, :cat, :month, :amt, :st, :uid, :notes $extraVals)"
        );
        $stmt->execute(array_merge([
            ':dept' => trim((string) $input['department']),
            ':cat' => isset($input['category_id']) && $input['category_id'] !== '' ? (int) $input['category_id'] : null,
            ':month' => $input['fiscal_month'],
            ':amt' => (float) $input['planned_amount'],
            ':st' => 'submitted',
            ':uid' => (int) $user['id'],
            ':notes' => trim((string) ($input['notes'] ?? '')),
        ], $extraParams));
        $id = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, (int) $user['id'], 'finance', 'create_budget', 'department_budgets', $id, null, $input, 'Created budget');
        Response::json(['success' => true, 'message' => 'Budget submitted', 'data' => ['id' => $id]], 201);
    }

    public function approveBudget(int $id, array $input): void
    {
        $decision = trim((string) ($input['decision'] ?? ''));
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            Response::json(['success' => false, 'message' => 'Decision must be approved or rejected'], 422);
            return;
        }
        $user = Auth::user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
            return;
        }
        if (!Auth::can('finance.approve')) {
            Response::json(['success' => false, 'message' => 'You do not have permission to approve budgets'], 403);
            return;
        }

        // Enforce workflow level
        $approvalLevel = 1;
        $isLastLevel = true;
        $nextLevel = $this->getNextApprovalLevel('budget', $id);
        if ($nextLevel !== null) {
            if (isset($nextLevel['done'])) {
                Response::json(['success' => false, 'message' => 'Budget already fully approved at all levels'], 422);
                return;
            }
            $approvalLevel = (int) $nextLevel['level_no'];
            $isLastLevel   = !empty($nextLevel['is_final']);
            $isAdmin       = strtolower($user['role'] ?? '') === 'admin';
            if (!$isAdmin && (int) ($user['role_id'] ?? 0) !== (int) $nextLevel['role_id']) {
                $rStmt = $this->pdo->prepare('SELECT name FROM roles WHERE id = :rid');
                $rStmt->execute([':rid' => $nextLevel['role_id']]);
                $roleName = $rStmt->fetchColumn() ?: ('Role #' . $nextLevel['role_id']);
                Response::json(['success' => false, 'message' => "Level {$approvalLevel} approval requires {$roleName}"], 403);
                return;
            }
        }

        // Check budget exists and is still in submitted state
        $budgetCheck = $this->pdo->prepare('SELECT id FROM department_budgets WHERE id = :id AND status = "submitted" LIMIT 1');
        $budgetCheck->execute([':id' => $id]);
        if (!$budgetCheck->fetch()) {
            Response::json(['success' => false, 'message' => 'Budget not found or not in submitted state'], 404);
            return;
        }

        // Update status only on final-level approval or rejection
        if ($decision === 'rejected' || ($decision === 'approved' && $isLastLevel)) {
            $effectiveStatus = $decision === 'approved' ? 'approved' : 'rejected';
            $this->pdo->prepare(
                'UPDATE department_budgets SET status = :st, approved_by = :uid, approved_at = NOW() WHERE id = :id'
            )->execute([':st' => $effectiveStatus, ':uid' => (int) $user['id'], ':id' => $id]);
        }
        // Intermediate approval: status stays 'submitted', tracked in approval_logs

        // Log the approval action
        $this->logApproval('budget', $id, $decision, (int) $user['id'], $input['notes'] ?? null, $approvalLevel);

        Audit::log($this->pdo, (int) $user['id'], 'finance', "budget_$decision", 'department_budgets', $id, null, [
            'decision' => $decision, 'level' => $approvalLevel
        ], "Budget $decision (L{$approvalLevel})");

        $levelMsg = $nextLevel !== null && !$isLastLevel && $decision === 'approved'
            ? " (Level {$approvalLevel}/{$nextLevel['total']} — awaiting next level)"
            : '';
        Response::json(['success' => true, 'message' => "Budget $decision{$levelMsg}"]);
    }

    /* ───── Budget Expense Items ───── */

    /** List individual expense items for a budget */
    public function listBudgetExpenses(int $budgetId): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT be.*, u.full_name AS recorded_by_name
             FROM budget_expenses be
             LEFT JOIN users u ON u.id = be.recorded_by
             WHERE be.budget_id = :bid
             ORDER BY be.expense_date DESC, be.id DESC'
        );
        $stmt->execute([':bid' => $budgetId]);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    /** Add a single expense item to an approved budget */
    public function addBudgetExpense(int $budgetId, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

        // Budget must exist and be in active state
        $budget = $this->pdo->prepare('SELECT * FROM department_budgets WHERE id = :id LIMIT 1');
        $budget->execute([':id' => $budgetId]);
        $budget = $budget->fetch();
        if (!$budget) {
            Response::json(['success' => false, 'message' => 'Budget not found'], 404);
            return;
        }
        if (!in_array($budget['status'], ['approved', 'expenses_added'], true)) {
            Response::json(['success' => false, 'message' => 'Budget must be approved/active to add expenses'], 422);
            return;
        }

        $itemName    = trim((string) ($input['item_name'] ?? ''));
        $amount      = (float) ($input['amount'] ?? 0);
        $expenseDate = trim((string) ($input['expense_date'] ?? date('Y-m-d')));
        $notes       = trim((string) ($input['notes'] ?? ''));

        if ($itemName === '' || $amount <= 0) {
            Response::json(['success' => false, 'message' => 'item_name and a positive amount are required'], 422);
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO budget_expenses (budget_id, item_name, amount, expense_date, notes, recorded_by)
             VALUES (:bid, :name, :amt, :edate, :notes, :uid)'
        );
        $stmt->execute([
            ':bid'   => $budgetId,
            ':name'  => $itemName,
            ':amt'   => $amount,
            ':edate' => $expenseDate,
            ':notes' => $notes,
            ':uid'   => (int) $user['id'],
        ]);
        $expId = (int) $this->pdo->lastInsertId();

        // Update actual_amount on the budget (sum of all expense items)
        $this->syncBudgetActual($budgetId);

        Audit::log($this->pdo, (int) $user['id'], 'finance', 'budget_expense_added', 'budget_expenses', $expId, null, $input, 'Budget expense added');
        Response::json(['success' => true, 'message' => 'Expense item added', 'data' => ['id' => $expId]], 201);
    }

    /** Delete a budget expense item (only if budget not closed) */
    public function deleteBudgetExpense(int $budgetId, int $expenseId): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

        $budget = $this->pdo->prepare('SELECT status FROM department_budgets WHERE id = :id');
        $budget->execute([':id' => $budgetId]);
        $budget = $budget->fetch();
        if (!$budget || $budget['status'] === 'closed') {
            Response::json(['success' => false, 'message' => 'Cannot delete expenses from a closed budget'], 422);
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM budget_expenses WHERE id = :eid AND budget_id = :bid');
        $stmt->execute([':eid' => $expenseId, ':bid' => $budgetId]);
        if ($stmt->rowCount() === 0) {
            Response::json(['success' => false, 'message' => 'Expense item not found'], 404);
            return;
        }

        $this->syncBudgetActual($budgetId);
        Audit::log($this->pdo, (int) $user['id'], 'finance', 'budget_expense_deleted', 'budget_expenses', $expenseId, null, null, 'Budget expense deleted');
        Response::json(['success' => true, 'message' => 'Expense item removed']);
    }

    /** Re-calculate actual_amount from budget_expenses sum */
    private function syncBudgetActual(int $budgetId): void
    {
        $sum = $this->pdo->prepare('SELECT COALESCE(SUM(amount), 0) AS total FROM budget_expenses WHERE budget_id = :bid');
        $sum->execute([':bid' => $budgetId]);
        $total = (float) $sum->fetch()['total'];
        $status = $total > 0 ? 'expenses_added' : 'approved';
        $this->pdo->prepare('UPDATE department_budgets SET actual_amount = :amt, status = :st WHERE id = :id AND status IN ("approved","expenses_added")')
            ->execute([':amt' => $total, ':st' => $status, ':id' => $budgetId]);
    }

    /** Legacy support — bulk set actual_amount (backward compat) */
    public function addBudgetActualExpenses(int $id, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }

        $actualAmount = (float) ($input['actual_amount'] ?? 0);
        $actualNotes  = trim((string) ($input['actual_notes'] ?? ''));

        if ($actualAmount < 0) {
            Response::json(['success' => false, 'message' => 'Amount cannot be negative'], 422);
            return;
        }

        $budget = $this->pdo->prepare('SELECT * FROM department_budgets WHERE id = :id LIMIT 1');
        $budget->execute([':id' => $id]);
        $budget = $budget->fetch();
        if (!$budget) { Response::json(['success' => false, 'message' => 'Budget not found'], 404); return; }
        if (!in_array($budget['status'], ['approved', 'expenses_added'], true)) {
            Response::json(['success' => false, 'message' => 'Budget must be approved first'], 422);
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE department_budgets SET actual_amount = :amt, actual_notes = :notes, status = "expenses_added" WHERE id = :id');
        $stmt->execute([':amt' => $actualAmount, ':notes' => $actualNotes, ':id' => $id]);
        Response::json(['success' => true, 'message' => 'Actual expenses recorded']);
    }

    /** Close budget: total used → finance_entries (expense) */
    public function closeBudget(int $id, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        $role = strtolower((string) ($user['role'] ?? ''));
        if (!str_contains($role, 'admin') && !str_contains($role, 'finance')) {
            Response::json(['success' => false, 'message' => 'Only Admin or Finance Officer can close budgets'], 403);
            return;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM department_budgets WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $budget = $stmt->fetch();
        if (!$budget) { Response::json(['success' => false, 'message' => 'Budget not found'], 404); return; }
        if (!in_array($budget['status'], ['approved', 'expenses_added'], true)) {
            Response::json(['success' => false, 'message' => 'Budget must be approved or active before closing'], 422);
            return;
        }

        // Use total from budget_expenses table (or actual_amount fallback)
        $sumRow = $this->pdo->prepare('SELECT COALESCE(SUM(amount),0) AS total FROM budget_expenses WHERE budget_id = :bid');
        $sumRow->execute([':bid' => $id]);
        $totalUsed = (float) $sumRow->fetch()['total'];
        if ($totalUsed <= 0) {
            $totalUsed = (float) $budget['actual_amount'];
        }

        $plannedAmount = (float) $budget['planned_amount'];
        $remaining = $plannedAmount - $totalUsed;
        $closingNotes = trim((string) ($input['notes'] ?? $budget['actual_notes'] ?? ''));

        $this->pdo->beginTransaction();
        try {
            $financeEntryId = null;

            if ($totalUsed > 0) {
                $categoryId = $budget['category_id'] ?? null;
                if (!$categoryId) {
                    $catRow = $this->pdo->query("SELECT id FROM finance_categories WHERE category_type='expense' AND is_active=1 ORDER BY id ASC LIMIT 1")->fetch();
                    $categoryId = $catRow ? $catRow['id'] : null;
                }
                if (!$categoryId) {
                    $this->pdo->rollBack();
                    Response::json(['success' => false, 'message' => 'No expense category found. Link a category to this budget first.'], 422);
                    return;
                }

                $month = $budget['fiscal_month'];
                $entryDate = $month . '-01';
                $entryNo   = 'BDGT-' . str_replace('-', '', $month) . '-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
                $desc = 'Budget Expenses: ' . $budget['department'] . ' (' . $month . ')'
                      . ($closingNotes ? ' — ' . $closingNotes : '')
                      . ' [Used: TZS ' . number_format($totalUsed, 0) . ' / Budget: TZS ' . number_format($plannedAmount, 0) . ']';

                $hasApprovalCol = $this->columnExists('finance_entries', 'approval_status');
                $approvalCol  = $hasApprovalCol ? ', approval_status' : '';
                $approvalVals = $hasApprovalCol ? ", 'approved'" : '';

                $ins = $this->pdo->prepare(
                    "INSERT INTO finance_entries
                       (entry_no, entry_date, category_id, amount, payment_method, source_type, description, recorded_by, approved_by, approved_at $approvalCol)
                     VALUES
                       (:eno, :edate, :cat, :amt, 'cash', 'system', :desc, :uid, :uid2, NOW() $approvalVals)"
                );
                $ins->execute([
                    ':eno'   => $entryNo, ':edate' => $entryDate, ':cat' => $categoryId,
                    ':amt'   => $totalUsed, ':desc' => $desc,
                    ':uid'   => (int) $user['id'], ':uid2' => (int) $user['id'],
                ]);
                $financeEntryId = (int) $this->pdo->lastInsertId();
            }

            $this->pdo->prepare(
                'UPDATE department_budgets SET status = "closed", actual_amount = :total, closed_at = NOW(), closed_by = :uid, finance_entry_id = :feid WHERE id = :id'
            )->execute([':total' => $totalUsed, ':uid' => (int) $user['id'], ':feid' => $financeEntryId, ':id' => $id]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
            return;
        }

        Audit::log($this->pdo, (int) $user['id'], 'finance', 'budget_closed', 'department_budgets', $id, null, [
            'total_used' => $totalUsed, 'planned_amount' => $plannedAmount,
            'remaining' => $remaining, 'finance_entry_id' => $financeEntryId,
        ], 'Budget closed and posted to expenses');

        Response::json(['success' => true, 'message' => 'Budget closed successfully', 'data' => [
            'finance_entry_id' => $financeEntryId, 'total_used' => $totalUsed,
            'planned_amount' => $plannedAmount, 'remaining' => $remaining,
        ]]);
    }

    /** Helper: check if a table exists */
    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t");
        $stmt->execute([':t' => $table]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /* ───── Member Contribution History ───── */

    public function memberContributions(int $memberId): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT fe.id, fe.entry_no, fe.entry_date, fc.name AS category_name, fc.category_type,
                    fe.amount, fe.payment_method, fe.description
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fe.member_id = :mid
             ORDER BY fe.entry_date DESC LIMIT 200"
        );
        $stmt->execute([':mid' => $memberId]);
        $entries = $stmt->fetchAll();

        $totals = $this->pdo->prepare(
            "SELECT fc.category_type, COALESCE(SUM(fe.amount),0) AS total
             FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id=fe.category_id
             WHERE fe.member_id = :mid
             GROUP BY fc.category_type"
        );
        $totals->execute([':mid' => $memberId]);

        $summary = ['income' => 0, 'expense' => 0];
        foreach ($totals->fetchAll() as $r) {
            $summary[$r['category_type']] = (float) $r['total'];
        }

        Response::json(['success' => true, 'data' => ['entries' => $entries, 'summary' => $summary]]);
    }

    /* ═══════════════════════════════════════════════════════
     *  APPROVAL ENGINE
     * ═══════════════════════════════════════════════════════ */

    /** Log an approval action */
    private function logApproval(string $entityType, int $entityId, string $action, int $actorId, ?string $notes = null, int $level = 1): void
    {
        try {
            $this->pdo->prepare(
                'INSERT INTO approval_logs (entity_type, entity_id, level_no, action, actor_id, notes) VALUES (:et, :eid, :lv, :act, :aid, :n)'
            )->execute([':et' => $entityType, ':eid' => $entityId, ':lv' => $level, ':act' => $action, ':aid' => $actorId, ':n' => $notes]);
        } catch (\Throwable $e) { /* table may not exist yet */ }
    }

    /**
     * Determine the next required approval level for a workflow entity.
     * Returns null if no workflow configured (fallback to permission-only).
     * Returns ['done'=>true] if all levels completed.
     * Returns ['level_no'=>L, 'role_id'=>R, 'total'=>N, 'is_final'=>bool] for next level.
     */
    private function getNextApprovalLevel(string $workflowType, int $entityId): ?array
    {
        if (!$this->tableExists('approval_workflows')) return null;

        $stmt = $this->pdo->prepare(
            'SELECT level_no, role_id FROM approval_workflows WHERE workflow_type = :wt AND is_active = 1 ORDER BY level_no ASC'
        );
        $stmt->execute([':wt' => $workflowType]);
        $levels = $stmt->fetchAll();
        if (empty($levels)) return null;

        $total = count($levels);

        // Only count approvals after the last rejection (handles resubmission)
        $lastReject = $this->pdo->prepare(
            "SELECT MAX(acted_at) FROM approval_logs WHERE entity_type = :et AND entity_id = :eid AND action = 'rejected'"
        );
        $lastReject->execute([':et' => $workflowType, ':eid' => $entityId]);
        $lastRejectTime = $lastReject->fetchColumn();

        $sql = "SELECT DISTINCT level_no FROM approval_logs WHERE entity_type = :et AND entity_id = :eid AND action = 'approved'";
        $params = [':et' => $workflowType, ':eid' => $entityId];
        if ($lastRejectTime) {
            $sql .= ' AND acted_at > :lr';
            $params[':lr'] = $lastRejectTime;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $completed = array_map('intval', array_column($stmt->fetchAll(), 'level_no'));

        foreach ($levels as $i => $lv) {
            if (!in_array((int) $lv['level_no'], $completed, true)) {
                return [
                    'level_no' => (int) $lv['level_no'],
                    'role_id'  => (int) $lv['role_id'],
                    'total'    => $total,
                    'is_final' => ($i === $total - 1),
                ];
            }
        }

        return ['done' => true, 'total' => $total];
    }

    /** Get approval history for an entity */
    public function getApprovalHistory(string $entityType, int $entityId): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT al.*, u.full_name AS actor_name
             FROM approval_logs al LEFT JOIN users u ON u.id = al.actor_id
             WHERE al.entity_type = :et AND al.entity_id = :eid
             ORDER BY al.acted_at ASC'
        );
        $stmt->execute([':et' => $entityType, ':eid' => $entityId]);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    /* ═══════════════════════════════════════════════════════
     *  PROCUREMENT MODULE
     * ═══════════════════════════════════════════════════════ */

    /** List purchase requests with filters */
    public function listPurchaseRequests(): void
    {
        $status   = trim((string) ($_GET['status'] ?? ''));
        $budgetId = trim((string) ($_GET['budget_id'] ?? ''));

        $sql = "SELECT pr.id, pr.request_no, pr.requested_by, pr.department, pr.purpose, pr.estimated_cost,
                       pr.event_id, pr.budget_id, pr.vendor_name, pr.approved_by, pr.approved_at,
                       pr.rejection_reason, pr.completed_at, pr.requested_date, pr.required_by_date, pr.status,
                       pr.created_at, pr.updated_at,
                       u.full_name AS requested_by_name,
                       a.full_name AS approved_by_name,
                       db.department AS budget_department, db.fiscal_month AS budget_month,
                       db.planned_amount AS budget_amount,
                       COALESCE(db.reserved_amount, 0) AS budget_reserved,
                       ev.title AS event_title,
                       COALESCE((SELECT SUM(quantity * estimated_unit_cost) FROM purchase_request_items WHERE purchase_request_id = pr.id), 0) AS items_total,
                       COALESCE((SELECT COUNT(*) FROM purchase_request_items WHERE purchase_request_id = pr.id), 0) AS item_count
                FROM purchase_requests pr
                LEFT JOIN users u ON u.id = pr.requested_by
                LEFT JOIN users a ON a.id = pr.approved_by
                LEFT JOIN department_budgets db ON db.id = pr.budget_id
                LEFT JOIN events ev ON ev.id = pr.event_id
                WHERE 1=1";
        $params = [];

        if ($status !== '') {
            // Allow comma-separated statuses
            $statuses = array_filter(array_map('trim', explode(',', $status)));
            if (count($statuses) === 1) {
                $sql .= ' AND pr.status = :st';
                $params[':st'] = $statuses[0];
            } elseif (count($statuses) > 1) {
                $placeholders = [];
                foreach ($statuses as $i => $s) {
                    $key = ":st$i";
                    $placeholders[] = $key;
                    $params[$key] = $s;
                }
                $sql .= ' AND pr.status IN (' . implode(',', $placeholders) . ')';
            }
        }
        if ($budgetId !== '') {
            $sql .= ' AND pr.budget_id = :bid';
            $params[':bid'] = (int) $budgetId;
        }

        $sql .= ' ORDER BY pr.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    /** Create a new purchase request with items */
    public function createPurchaseRequest(array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('procurement.request.create')) {
            Response::json(['success' => false, 'message' => 'No permission to create procurement requests'], 403); return;
        }

        $budgetId = (int) ($input['budget_id'] ?? 0);
        $purpose  = trim((string) ($input['purpose'] ?? ''));
        $items    = $input['items'] ?? [];

        if ($budgetId <= 0) {
            Response::json(['success' => false, 'message' => 'An approved budget must be selected'], 422); return;
        }
        if ($purpose === '') {
            Response::json(['success' => false, 'message' => 'Purpose is required'], 422); return;
        }
        if (empty($items)) {
            Response::json(['success' => false, 'message' => 'At least one item is required'], 422); return;
        }

        // Verify budget exists and is approved/active
        $budget = $this->pdo->prepare('SELECT * FROM department_budgets WHERE id = :id AND status IN ("approved","expenses_added") LIMIT 1');
        $budget->execute([':id' => $budgetId]);
        $budget = $budget->fetch();
        if (!$budget) {
            Response::json(['success' => false, 'message' => 'Budget not found or not in active state'], 422); return;
        }

        // Calculate total from items
        $totalCost = 0;
        foreach ($items as $item) {
            $qty  = (float) ($item['quantity'] ?? 1);
            $cost = (float) ($item['estimated_unit_cost'] ?? 0);
            $totalCost += $qty * $cost;
        }

        // Check budget has enough room (planned - actual - reserved)
        $planned  = (float) $budget['planned_amount'];
        $spent    = (float) ($budget['actual_amount'] ?? 0);
        $reserved = (float) ($budget['reserved_amount'] ?? 0);
        $available = $planned - $spent - $reserved;

        if ($totalCost > $available) {
            Response::json(['success' => false, 'message' => "Insufficient budget. Available: " . number_format($available, 2) . ", Requested: " . number_format($totalCost, 2)], 422);
            return;
        }

        // Generate request number
        $reqNo = 'PR-' . date('Ymd') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO purchase_requests (request_no, requested_by, department, purpose, estimated_cost, event_id, budget_id, vendor_name, requested_date, status)
                 VALUES (:rno, :uid, :dept, :purpose, :cost, :eid, :bid, :vendor, CURDATE(), "submitted")'
            );
            $stmt->execute([
                ':rno'     => $reqNo,
                ':uid'     => (int) $user['id'],
                ':dept'    => $budget['department'],
                ':purpose' => $purpose,
                ':cost'    => $totalCost,
                ':eid'     => ($input['event_id'] ?? null) ?: ($budget['event_id'] ?: null),
                ':bid'     => $budgetId,
                ':vendor'  => trim((string) ($input['vendor_name'] ?? '')),
            ]);
            $prId = (int) $this->pdo->lastInsertId();

            // Insert line items
            $itemStmt = $this->pdo->prepare(
                'INSERT INTO purchase_request_items (purchase_request_id, item_name, quantity, estimated_unit_cost, notes)
                 VALUES (:prid, :name, :qty, :cost, :notes)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    ':prid'  => $prId,
                    ':name'  => trim((string) ($item['item_name'] ?? 'Item')),
                    ':qty'   => (float) ($item['quantity'] ?? 1),
                    ':cost'  => (float) ($item['estimated_unit_cost'] ?? 0),
                    ':notes' => trim((string) ($item['notes'] ?? '')),
                ]);
            }

            $this->logApproval('procurement', $prId, 'submitted', (int) $user['id'], 'Request created');
            Audit::log($this->pdo, (int) $user['id'], 'procurement', 'create_request', 'purchase_requests', $prId, null, ['total' => $totalCost], "PR $reqNo created");

            $this->pdo->commit();
            Response::json(['success' => true, 'message' => 'Procurement request created', 'data' => ['id' => $prId, 'request_no' => $reqNo]]);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed to create request: ' . $e->getMessage()], 500);
        }
    }

    /** Get purchase request detail with items */
    public function getPurchaseRequestDetail(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT pr.*, u.full_name AS requested_by_name,
                    db.department AS budget_department, db.fiscal_month AS budget_month,
                    db.planned_amount AS budget_amount,
                    ev.title AS event_title,
                    a.full_name AS approved_by_name
             FROM purchase_requests pr
             LEFT JOIN users u ON u.id = pr.requested_by
             LEFT JOIN users a ON a.id = pr.approved_by
             LEFT JOIN department_budgets db ON db.id = pr.budget_id
             LEFT JOIN events ev ON ev.id = pr.event_id
             WHERE pr.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $pr = $stmt->fetch();
        if (!$pr) { Response::json(['success' => false, 'message' => 'Not found'], 404); return; }

        $items = $this->pdo->prepare('SELECT * FROM purchase_request_items WHERE purchase_request_id = :id ORDER BY id');
        $items->execute([':id' => $id]);
        $pr['items'] = $items->fetchAll();

        // Approval history
        $history = $this->pdo->prepare(
            'SELECT al.*, u.full_name AS actor_name FROM approval_logs al LEFT JOIN users u ON u.id = al.actor_id WHERE al.entity_type = "procurement" AND al.entity_id = :id ORDER BY al.acted_at'
        );
        $history->execute([':id' => $id]);
        $pr['approval_history'] = $history->fetchAll();

        Response::json(['success' => true, 'data' => $pr]);
    }

    /** Approve or reject a purchase request */
    public function approvePurchaseRequest(int $id, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('procurement.request.approve')) {
            Response::json(['success' => false, 'message' => 'No permission to approve procurement requests'], 403); return;
        }

        $decision = trim((string) ($input['decision'] ?? ''));
        $notes    = trim((string) ($input['notes'] ?? ''));
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            Response::json(['success' => false, 'message' => 'Decision must be approved or rejected'], 422); return;
        }

        // Enforce workflow level
        $approvalLevel = 1;
        $isLastLevel = true;
        $nextLevel = $this->getNextApprovalLevel('procurement', $id);
        if ($nextLevel !== null) {
            if (isset($nextLevel['done'])) {
                Response::json(['success' => false, 'message' => 'Request already fully approved at all levels'], 422); return;
            }
            $approvalLevel = (int) $nextLevel['level_no'];
            $isLastLevel   = !empty($nextLevel['is_final']);
            $isAdmin       = strtolower($user['role'] ?? '') === 'admin';
            if (!$isAdmin && (int) ($user['role_id'] ?? 0) !== (int) $nextLevel['role_id']) {
                $rStmt = $this->pdo->prepare('SELECT name FROM roles WHERE id = :rid');
                $rStmt->execute([':rid' => $nextLevel['role_id']]);
                $roleName = $rStmt->fetchColumn() ?: ('Role #' . $nextLevel['role_id']);
                Response::json(['success' => false, 'message' => "Level {$approvalLevel} approval requires {$roleName}"], 403); return;
            }
        }

        $pr = $this->pdo->prepare('SELECT * FROM purchase_requests WHERE id = :id AND status = "submitted" LIMIT 1');
        $pr->execute([':id' => $id]);
        $pr = $pr->fetch();
        if (!$pr) { Response::json(['success' => false, 'message' => 'Request not found or not pending'], 404); return; }

        $this->pdo->beginTransaction();
        try {
            if ($decision === 'approved' && $isLastLevel) {
                // Final level — reserve budget and mark approved
                $budgetId = (int) $pr['budget_id'];
                if ($budgetId > 0) {
                    $this->pdo->prepare(
                        'UPDATE department_budgets SET reserved_amount = reserved_amount + :amt WHERE id = :bid'
                    )->execute([':amt' => (float) $pr['estimated_cost'], ':bid' => $budgetId]);
                }

                $this->pdo->prepare(
                    'UPDATE purchase_requests SET status = "approved", approved_by = :uid, approved_at = NOW(), rejection_reason = NULL WHERE id = :id'
                )->execute([':uid' => (int) $user['id'], ':id' => $id]);
            } elseif ($decision === 'rejected') {
                $this->pdo->prepare(
                    'UPDATE purchase_requests SET status = "rejected", approved_by = :uid, approved_at = NOW(), rejection_reason = :reason WHERE id = :id'
                )->execute([':uid' => (int) $user['id'], ':id' => $id, ':reason' => $notes]);
            }
            // Intermediate approval: status stays 'submitted', tracked in approval_logs

            $this->logApproval('procurement', $id, $decision, (int) $user['id'], $notes, $approvalLevel);
            Audit::log($this->pdo, (int) $user['id'], 'procurement', "request_$decision", 'purchase_requests', $id, null, [
                'decision' => $decision, 'level' => $approvalLevel
            ], "PR $decision (L{$approvalLevel})");

            $this->pdo->commit();
            $levelMsg = $nextLevel !== null && !$isLastLevel && $decision === 'approved'
                ? " (Level {$approvalLevel}/{$nextLevel['total']} — awaiting next level)"
                : '';
            Response::json(['success' => true, 'message' => "Request $decision{$levelMsg}"]);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /** Mark a procurement request as purchased — converts to actual expenses */
    public function markPurchased(int $id, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('procurement.po.complete')) {
            Response::json(['success' => false, 'message' => 'No permission to mark purchases'], 403); return;
        }

        $pr = $this->pdo->prepare('SELECT * FROM purchase_requests WHERE id = :id AND status = "approved" LIMIT 1');
        $pr->execute([':id' => $id]);
        $pr = $pr->fetch();
        if (!$pr) { Response::json(['success' => false, 'message' => 'Request not found or not in approved state'], 404); return; }

        $budgetId = (int) $pr['budget_id'];

        // Get items
        $items = $this->pdo->prepare('SELECT * FROM purchase_request_items WHERE purchase_request_id = :id');
        $items->execute([':id' => $id]);
        $items = $items->fetchAll();

        $this->pdo->beginTransaction();
        try {
            // Update status
            $this->pdo->prepare('UPDATE purchase_requests SET status = "purchased", completed_at = NOW() WHERE id = :id')
                ->execute([':id' => $id]);

            // Release reserved amount and create actual expenses
            if ($budgetId > 0) {
                $totalCost = (float) $pr['estimated_cost'];
                // Release reserved amount
                $this->pdo->prepare(
                    'UPDATE department_budgets SET reserved_amount = GREATEST(0, reserved_amount - :amt) WHERE id = :bid'
                )->execute([':amt' => $totalCost, ':bid' => $budgetId]);

                // Create budget expense entries from items
                $expStmt = $this->pdo->prepare(
                    'INSERT INTO budget_expenses (budget_id, item_name, amount, expense_date, notes, source_type, source_id, recorded_by)
                     VALUES (:bid, :name, :amt, CURDATE(), :notes, "procurement", :sid, :uid)'
                );
                foreach ($items as $item) {
                    $expStmt->execute([
                        ':bid'   => $budgetId,
                        ':name'  => $item['item_name'],
                        ':amt'   => (float) $item['line_total'],
                        ':notes' => 'From PR ' . $pr['request_no'],
                        ':sid'   => $id,
                        ':uid'   => (int) $user['id'],
                    ]);
                }

                // Sync actual_amount
                $this->syncBudgetActual($budgetId);
            }

            $this->logApproval('procurement', $id, 'approved', (int) $user['id'], 'Purchased — expenses recorded to budget');
            Audit::log($this->pdo, (int) $user['id'], 'procurement', 'mark_purchased', 'purchase_requests', $id, null, null, "PR marked purchased, expenses created");

            $this->pdo->commit();
            Response::json(['success' => true, 'message' => 'Marked as purchased — expenses recorded to budget']);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /** Complete a procurement request */
    public function completePurchaseRequest(int $id): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('procurement.po.complete')) {
            Response::json(['success' => false, 'message' => 'No permission to complete procurement requests'], 403); return;
        }

        $stmt = $this->pdo->prepare('UPDATE purchase_requests SET status = "completed", completed_at = NOW() WHERE id = :id AND status = "purchased"');
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            Response::json(['success' => false, 'message' => 'Request not found or not in purchased state'], 404); return;
        }

        $this->logApproval('procurement', $id, 'approved', (int) $user['id'], 'Completed');
        Audit::log($this->pdo, (int) $user['id'], 'procurement', 'complete', 'purchase_requests', $id, null, null, "PR completed");
        Response::json(['success' => true, 'message' => 'Procurement completed']);
    }

    /** Cancel a procurement request — release reserved amount if applicable */
    public function cancelPurchaseRequest(int $id, array $input): void
    {
        $user = Auth::user();
        if (!$user) { Response::json(['success' => false, 'message' => 'Not authenticated'], 401); return; }
        if (!Auth::can('procurement.request.create')) {
            Response::json(['success' => false, 'message' => 'No permission to cancel procurement requests'], 403); return;
        }

        $pr = $this->pdo->prepare('SELECT * FROM purchase_requests WHERE id = :id AND status NOT IN ("completed","cancelled") LIMIT 1');
        $pr->execute([':id' => $id]);
        $pr = $pr->fetch();
        if (!$pr) { Response::json(['success' => false, 'message' => 'Cannot cancel'], 404); return; }

        $budgetId = (int) ($pr['budget_id'] ?? 0);

        $this->pdo->beginTransaction();
        try {
            // If it was approved (reserved), release the reserved amount
            if (in_array($pr['status'], ['approved'], true) && $budgetId > 0) {
                $this->pdo->prepare(
                    'UPDATE department_budgets SET reserved_amount = GREATEST(0, reserved_amount - :amt) WHERE id = :bid'
                )->execute([':amt' => (float) $pr['estimated_cost'], ':bid' => $budgetId]);
            }

            // If it was purchased, clean up the budget_expenses that were created
            if ($pr['status'] === 'purchased' && $budgetId > 0) {
                $this->pdo->prepare(
                    'DELETE FROM budget_expenses WHERE source_type = "procurement" AND source_id = :sid AND budget_id = :bid'
                )->execute([':sid' => $id, ':bid' => $budgetId]);
                // Re-sync actual_amount on the budget
                $this->syncBudgetActual($budgetId);
            }

            $this->pdo->prepare('UPDATE purchase_requests SET status = "cancelled" WHERE id = :id')->execute([':id' => $id]);
            $this->logApproval('procurement', $id, 'rejected', (int) $user['id'], trim((string) ($input['reason'] ?? 'Cancelled')));
            Audit::log($this->pdo, (int) $user['id'], 'procurement', 'cancel', 'purchase_requests', $id, null, null, "PR cancelled");

            $this->pdo->commit();
            Response::json(['success' => true, 'message' => 'Request cancelled']);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /** List active budgets available for procurement (approved, with remaining balance) */
    public function listActiveBudgetsForProcurement(): void
    {
        $sql = "SELECT db.id, db.department, db.fiscal_month, db.planned_amount,
                       COALESCE(db.actual_amount, 0) AS actual_amount,
                       COALESCE(db.reserved_amount, 0) AS reserved_amount,
                       (db.planned_amount - COALESCE(db.actual_amount, 0) - COALESCE(db.reserved_amount, 0)) AS available,
                       db.event_id, ev.title AS event_title, db.description
                FROM department_budgets db
                LEFT JOIN events ev ON ev.id = db.event_id
                WHERE db.status IN ('approved', 'expenses_added')
                ORDER BY db.fiscal_month DESC, db.department ASC";
        $rows = $this->pdo->query($sql)->fetchAll();
        Response::json(['success' => true, 'data' => $rows]);
    }

    /* ═══════════════════════════════════════════════════════
     *  SETTINGS: APPROVAL WORKFLOWS & ROLES
     * ═══════════════════════════════════════════════════════ */

    /** List approval workflows */
    public function listApprovalWorkflows(): void
    {
        try {
            if (!$this->tableExists('approval_workflows')) {
                Response::json(['success' => true, 'data' => []]);
                return;
            }
            $stmt = $this->pdo->query(
                'SELECT aw.*, r.name AS role_name
                 FROM approval_workflows aw
                 INNER JOIN roles r ON r.id = aw.role_id
                 ORDER BY aw.workflow_type, aw.level_no'
            );
            Response::json(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /** Save (upsert) an approval workflow level */
    public function saveApprovalWorkflow(array $input): void
    {
        if (!Auth::can('settings.manage')) {
            Response::json(['success' => false, 'message' => 'No permission'], 403); return;
        }
        $type   = trim((string) ($input['workflow_type'] ?? ''));
        $level  = (int) ($input['level_no'] ?? 1);
        $roleId = (int) ($input['role_id'] ?? 0);

        if (!in_array($type, ['budget', 'procurement', 'finance_entry'], true) || $roleId <= 0) {
            Response::json(['success' => false, 'message' => 'Invalid input'], 422); return;
        }

        $this->pdo->prepare(
            'INSERT INTO approval_workflows (workflow_type, level_no, role_id)
             VALUES (:t, :l, :r)
             ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)'
        )->execute([':t' => $type, ':l' => $level, ':r' => $roleId]);

        Response::json(['success' => true, 'message' => 'Approval workflow saved']);
    }

    /** Delete an approval workflow level */
    public function deleteApprovalWorkflow(int $id): void
    {
        if (!Auth::can('settings.manage')) {
            Response::json(['success' => false, 'message' => 'No permission'], 403); return;
        }
        $this->pdo->prepare('DELETE FROM approval_workflows WHERE id = :id')->execute([':id' => $id]);
        Response::json(['success' => true, 'message' => 'Deleted']);
    }

    /** List all roles with their permissions */
    public function listRolesWithPermissions(): void
    {
        try {
            $roles = $this->pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
            
            // Safely get permissions if role_permissions table exists
            $perms = [];
            if ($this->tableExists('role_permissions')) {
                $perms = $this->pdo->query(
                    'SELECT rp.role_id, p.id AS permission_id, p.name, p.module, p.description
                     FROM role_permissions rp
                     INNER JOIN permissions p ON p.id = rp.permission_id
                     ORDER BY p.module, p.name'
                )->fetchAll();
            }

            $grouped = [];
            foreach ($perms as $p) {
                $grouped[(int) $p['role_id']][] = $p;
            }

            foreach ($roles as &$r) {
                $r['permissions'] = $grouped[(int) $r['id']] ?? [];
            }

            Response::json(['success' => true, 'data' => $roles]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /** List all available permissions */
    public function listPermissions(): void
    {
        try {
            if (!$this->tableExists('permissions')) {
                Response::json(['success' => true, 'data' => []]);
                return;
            }
            $rows = $this->pdo->query('SELECT * FROM permissions ORDER BY module, name')->fetchAll();
            Response::json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /** Update permissions for a role */
    public function updateRolePermissions(int $roleId, array $input): void
    {
        if (!Auth::can('settings.manage')) {
            Response::json(['success' => false, 'message' => 'No permission'], 403); return;
        }
        $permissionIds = $input['permission_ids'] ?? [];
        if (!is_array($permissionIds)) {
            Response::json(['success' => false, 'message' => 'permission_ids must be an array'], 422); return;
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM role_permissions WHERE role_id = :rid')->execute([':rid' => $roleId]);
            $ins = $this->pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:rid, :pid)');
            foreach ($permissionIds as $pid) {
                $ins->execute([':rid' => $roleId, ':pid' => (int) $pid]);
            }
            $this->pdo->commit();
            Response::json(['success' => true, 'message' => 'Permissions updated']);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  UNIFIED BUDGET-PROCUREMENT REPORT
     * ═══════════════════════════════════════════════════════ */

    public function budgetProcurementReport(): void
    {
        $month = trim((string) ($_GET['month'] ?? ''));
        $event = trim((string) ($_GET['event_id'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $sql = "SELECT db.id, db.department, db.fiscal_month, db.planned_amount,
                       COALESCE(db.actual_amount, 0) AS spent,
                       COALESCE(db.reserved_amount, 0) AS reserved,
                       (db.planned_amount - COALESCE(db.actual_amount, 0) - COALESCE(db.reserved_amount, 0)) AS remaining,
                       db.status, db.event_id, ev.title AS event_title,
                       (SELECT COUNT(*) FROM purchase_requests pr WHERE pr.budget_id = db.id) AS pr_count,
                       (SELECT COALESCE(SUM(pr2.estimated_cost), 0) FROM purchase_requests pr2 WHERE pr2.budget_id = db.id AND pr2.status IN ('approved','purchased','completed')) AS pr_total
                FROM department_budgets db
                LEFT JOIN events ev ON ev.id = db.event_id
                WHERE db.status NOT IN ('draft')";
        $params = [];
        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $sql .= ' AND db.fiscal_month = :m';
            $params[':m'] = $month;
        }
        if ($event !== '') { $sql .= ' AND db.event_id = :eid'; $params[':eid'] = (int) $event; }
        if ($status !== '') { $sql .= ' AND db.status = :st'; $params[':st'] = $status; }
        $sql .= ' ORDER BY db.fiscal_month DESC, db.department';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Totals
        $totals = ['budgets' => count($rows), 'approved' => 0, 'spent' => 0, 'reserved' => 0, 'remaining' => 0];
        foreach ($rows as $r) {
            $totals['approved']  += (float) $r['planned_amount'];
            $totals['spent']     += (float) $r['spent'];
            $totals['reserved']  += (float) $r['reserved'];
            $totals['remaining'] += (float) $r['remaining'];
        }

        Response::json(['success' => true, 'data' => ['rows' => $rows, 'totals' => $totals]]);
    }

    /* ═══════════ Unified Reports Dashboard ═══════════ */

    /**
     * Unified financial report: Combines budgets, procurement, events, and finance entries
     * into one consolidated view.
     * Filters: ?date_from=&date_to=&event_id=&status=&department=
     */
    public function reportsDashboard(): void
    {
        $dateFrom   = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo     = trim((string) ($_GET['date_to'] ?? ''));
        $eventId    = trim((string) ($_GET['event_id'] ?? ''));
        $status     = trim((string) ($_GET['status'] ?? ''));
        $department = trim((string) ($_GET['department'] ?? ''));

        // Set default date range if not provided
        if (empty($dateFrom)) { $dateFrom = date('Y-m-d', strtotime('-6 weeks')); }
        if (empty($dateTo))   { $dateTo = date('Y-m-d'); }

        // ── 1. Budgets with full financial picture ──
        $sql = "SELECT
                    db.id AS budget_id,
                    db.department,
                    DATE(COALESCE(db.created_at, NOW())) as budget_date,
                    DATE_FORMAT(DATE(COALESCE(db.created_at, NOW())), '%Y-W%v') as week_label,
                    db.planned_amount AS budget_requested,
                    db.planned_amount AS budget_approved,
                    COALESCE(db.actual_amount, 0) AS total_expenses,
                    COALESCE(db.reserved_amount, 0) AS reserved,
                    (db.planned_amount - COALESCE(db.actual_amount, 0) - COALESCE(db.reserved_amount, 0)) AS remaining_balance,
                    db.status,
                    db.event_id,
                    ev.title AS event_name,
                    ev.start_datetime AS event_date,
                    COALESCE(u.full_name, '') AS submitted_by_name,
                    COALESCE(ua.full_name, '') AS approved_by_name,
                    db.created_at,
                    db.approved_at,
                    db.closed_at,
                    db.description,
                    -- Procurement sub-totals
                    (SELECT COUNT(*) FROM purchase_requests pr WHERE pr.budget_id = db.id) AS pr_count,
                    (SELECT COALESCE(SUM(pr2.estimated_cost), 0)
                     FROM purchase_requests pr2
                     WHERE pr2.budget_id = db.id AND pr2.status IN ('approved','purchased','completed')) AS pr_approved_total,
                    -- Finance entry count linked to this budget
                    (SELECT COUNT(*) FROM budget_expenses be WHERE be.budget_id = db.id) AS expense_item_count
                FROM department_budgets db
                LEFT JOIN events ev ON ev.id = db.event_id
                LEFT JOIN users u ON u.id = db.submitted_by
                LEFT JOIN users ua ON ua.id = db.approved_by
                WHERE db.status NOT IN ('draft')";
        $params = [];

        if ($dateFrom !== '') {
            $sql .= " AND DATE(COALESCE(db.created_at, NOW())) >= :df";
            $params[':df'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $sql .= " AND DATE(COALESCE(db.created_at, NOW())) <= :dt";
            $params[':dt'] = $dateTo;
        }
        if ($eventId !== '') {
            $sql .= " AND db.event_id = :eid";
            $params[':eid'] = (int) $eventId;
        }
        if ($status !== '') {
            $sql .= " AND db.status = :st";
            $params[':st'] = $status;
        }
        if ($department !== '') {
            $sql .= " AND db.department = :dept";
            $params[':dept'] = $department;
        }

        $sql .= " ORDER BY budget_date DESC, db.department";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // ── 2. Aggregated KPIs ──
        $kpi = [
            'total_budgets'   => count($rows),
            'total_requested' => 0,
            'total_approved'  => 0,
            'total_expenses'  => 0,
            'total_reserved'  => 0,
            'total_remaining' => 0,
            'total_pr_count'  => 0,
            'total_pr_amount' => 0,
            'by_status'       => [],
        ];
        foreach ($rows as $r) {
            $kpi['total_requested'] += (float) $r['budget_requested'];
            $kpi['total_approved']  += (float) $r['budget_approved'];
            $kpi['total_expenses']  += (float) $r['total_expenses'];
            $kpi['total_reserved']  += (float) $r['reserved'];
            $kpi['total_remaining'] += (float) $r['remaining_balance'];
            $kpi['total_pr_count']  += (int) $r['pr_count'];
            $kpi['total_pr_amount'] += (float) $r['pr_approved_total'];
            $st = $r['status'];
            $kpi['by_status'][$st] = ($kpi['by_status'][$st] ?? 0) + 1;
        }

        // ── 3. Available filter options ──
        $deptStmt = $this->pdo->query("SELECT DISTINCT department FROM department_budgets WHERE status != 'draft' ORDER BY department");
        $departments = $deptStmt->fetchAll(\PDO::FETCH_COLUMN);

        $evtStmt = $this->pdo->query(
            "SELECT DISTINCT ev.id, ev.title FROM events ev
             INNER JOIN department_budgets db ON db.event_id = ev.id
             WHERE db.status != 'draft' ORDER BY ev.title"
        );
        $events = $evtStmt->fetchAll();

        // ── 4. Weekly trend (last 6 weeks) ──
        $trendStmt = $this->pdo->query(
            "SELECT DATE_FORMAT(DATE(COALESCE(db.created_at, NOW())), '%Y-W%v') as week_label,
                    MIN(DATE(COALESCE(db.created_at, NOW()))) as week_start,
                    SUM(db.planned_amount) AS approved,
                    SUM(COALESCE(db.actual_amount, 0)) AS spent,
                    SUM(COALESCE(db.reserved_amount, 0)) AS reserved
             FROM department_budgets db
             WHERE db.status NOT IN ('draft','rejected')
             GROUP BY YEARWEEK(COALESCE(db.created_at, NOW()))
             ORDER BY week_start DESC
             LIMIT 6"
        );
        $trend = array_reverse($trendStmt->fetchAll());

        // ── 5. Finance entries summary for the filtered period ──
        $feSql = "SELECT
                    COALESCE(SUM(CASE WHEN fc.category_type = 'income' THEN fe.amount ELSE 0 END), 0) AS total_income,
                    COALESCE(SUM(CASE WHEN fc.category_type = 'expense' THEN fe.amount ELSE 0 END), 0) AS total_expense
                  FROM finance_entries fe
                  JOIN finance_categories fc ON fc.id = fe.category_id
                  WHERE 1=1";
        $feParams = [];
        if ($this->columnExists('finance_entries', 'approval_status')) {
            $feSql .= " AND (fe.approval_status = 'approved' OR fe.approval_status IS NULL)";
        }
        if ($dateFrom !== '') {
            $feSql .= " AND fe.entry_date >= :fdf";
            $feParams[':fdf'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $feSql .= " AND fe.entry_date <= :fdt";
            $feParams[':fdt'] = $dateTo;
        }
        $feStmt = $this->pdo->prepare($feSql);
        $feStmt->execute($feParams);
        $financeSummary = $feStmt->fetch();

        Response::json([
            'success' => true,
            'data'    => [
                'rows'            => $rows,
                'kpi'             => $kpi,
                'trend'           => $trend,
                'finance_summary' => $financeSummary,
                'filters'         => [
                    'departments' => $departments,
                    'events'      => $events,
                    'statuses'    => ['submitted', 'approved', 'expenses_added', 'closed', 'rejected'],
                ],
            ],
        ]);
    }

    /**
     * Export unified report as CSV
     * Same filters as reportsDashboard()
     */
    public function exportReportCsv(): void
    {
        $dateFrom   = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo     = trim((string) ($_GET['date_to'] ?? ''));
        $eventId    = trim((string) ($_GET['event_id'] ?? ''));
        $status     = trim((string) ($_GET['status'] ?? ''));
        $department = trim((string) ($_GET['department'] ?? ''));

        // Set default date range if not provided
        if (empty($dateFrom)) { $dateFrom = date('Y-m-d', strtotime('-6 weeks')); }
        if (empty($dateTo))   { $dateTo = date('Y-m-d'); }

        $sql = "SELECT
                    db.department,
                    COALESCE(ev.title, '—') AS event_name,
                    DATE_FORMAT(DATE(COALESCE(db.created_at, NOW())), '%Y-W%v') as week_label,
                    db.planned_amount AS budget_requested,
                    db.planned_amount AS budget_approved,
                    COALESCE(db.actual_amount, 0) AS total_expenses,
                    COALESCE(db.reserved_amount, 0) AS reserved,
                    (db.planned_amount - COALESCE(db.actual_amount, 0) - COALESCE(db.reserved_amount, 0)) AS remaining_balance,
                    db.status,
                    (SELECT COUNT(*) FROM purchase_requests pr WHERE pr.budget_id = db.id) AS procurement_requests,
                    COALESCE(db.description, '') AS description
                FROM department_budgets db
                LEFT JOIN events ev ON ev.id = db.event_id
                WHERE db.status NOT IN ('draft')";
        $params = [];
        if ($dateFrom !== '') { $sql .= " AND DATE(COALESCE(db.created_at, NOW())) >= :df"; $params[':df'] = $dateFrom; }
        if ($dateTo !== '')   { $sql .= " AND DATE(COALESCE(db.created_at, NOW())) <= :dt"; $params[':dt'] = $dateTo; }
        if ($eventId !== '')  { $sql .= " AND db.event_id = :eid"; $params[':eid'] = (int) $eventId; }
        if ($status !== '')   { $sql .= " AND db.status = :st"; $params[':st'] = $status; }
        if ($department !== '') { $sql .= " AND db.department = :dept"; $params[':dept'] = $department; }
        $sql .= " ORDER BY COALESCE(db.created_at, NOW()) DESC, db.department";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $filename = 'financial_report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Department', 'Event', 'Week', 'Budget Requested (TZS)', 'Budget Approved (TZS)', 'Total Expenses (TZS)', 'Reserved (TZS)', 'Remaining (TZS)', 'Status', 'Procurement Requests', 'Description']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['department'],
                $r['event_name'],
                $r['week_label'],
                number_format((float) $r['budget_requested'], 2, '.', ''),
                number_format((float) $r['budget_approved'], 2, '.', ''),
                number_format((float) $r['total_expenses'], 2, '.', ''),
                number_format((float) $r['reserved'], 2, '.', ''),
                number_format((float) $r['remaining_balance'], 2, '.', ''),
                $r['status'],
                $r['procurement_requests'],
                $r['description'],
            ]);
        }

        // Summary row
        $totReq = array_sum(array_column($rows, 'budget_requested'));
        $totApp = array_sum(array_column($rows, 'budget_approved'));
        $totExp = array_sum(array_column($rows, 'total_expenses'));
        $totRes = array_sum(array_column($rows, 'reserved'));
        $totRem = array_sum(array_column($rows, 'remaining_balance'));
        fputcsv($out, ['TOTALS', '', '', number_format($totReq, 2, '.', ''), number_format($totApp, 2, '.', ''), number_format($totExp, 2, '.', ''), number_format($totRes, 2, '.', ''), number_format($totRem, 2, '.', ''), '', '', '']);

        fclose($out);
        exit;
    }

    /**
     * Attendance Report Dashboard - Demographics
     * GET /api/v1/reports/attendance
     */
    public function attendanceReport(): void
    {
        Response::json(['success' => false, 'message' => 'Attendance reports have been removed'], 404);
    }

    /**
     * Export Attendance Report as CSV
     * GET /api/v1/reports/attendance/export/csv
     */
    public function exportAttendanceReportCsv(): void
    {
        Response::json(['success' => false, 'message' => 'Attendance reports have been removed'], 404);
    }

    /* ───── Fallback ───── */

    public function notFound(): void
    {
        Response::json(['success' => false, 'message' => 'Endpoint not found'], 404);
    }

    private function resolveEventKind(string $category, string $notes = ''): string
    {
        if (str_contains(strtolower($notes), '[event_subtype:appointment]')) {
            return 'appointment';
        }

        return match ($category) {
            'youth' => 'youth',
            'conference', 'seminar', 'choir' => 'worship',
            default => 'special',
        };
    }

    private function kindTag(string $kind): string
    {
        return match ($kind) {
            'worship' => 'Worship',
            'youth' => 'Youth',
            'appointment' => 'Appointment',
            default => 'Special',
        };
    }

    private function buildSystemChurchEvents(string $month): array
    {
        $year = (int) substr($month, 0, 4);
        $monthNum = (int) substr($month, 5, 2);
        $events = [];

        foreach ($this->sundaysInMonth($year . '-' . str_pad((string) $monthNum, 2, '0', STR_PAD_LEFT) . '-01') as $sundayDate) {
            $events[] = $this->buildSystemEvent($sundayDate, 'Worship Service', 'worship', 'Every Sunday worship gathering', '09:00:00', '11:30:00');
        }

        $easterDate = date('Y-m-d', easter_date($year));
        $goodFriday = date('Y-m-d', strtotime($easterDate . ' -2 days'));

        $fixedSpecials = [
            [$goodFriday, 'Good Friday Service'],
            [$easterDate, 'Easter Sunday Celebration'],
            [$year . '-12-25', 'Christmas Service'],
            [$year . '-01-01', 'New Year Service'],
        ];

        foreach ($fixedSpecials as [$date, $title]) {
            if ((int) substr($date, 5, 2) === $monthNum) {
                $events[] = $this->buildSystemEvent($date, $title, 'special', 'Auto-generated church calendar event', '09:00:00', '11:30:00');
            }
        }

        return $events;
    }

    private function buildSystemEvent(string $date, string $title, string $kind, string $notes, string $startTime, string $endTime): array
    {
        return [
            'id' => 'sys-' . str_replace('-', '', $date) . '-' . strtolower(str_replace(' ', '-', $title)),
            'event_code' => 'SYS-' . str_replace('-', '', $date),
            'title' => $title,
            'category' => 'system',
            'kind' => $kind,
            'tag' => $this->kindTag($kind),
            'start_datetime' => $date . ' ' . $startTime,
            'end_datetime' => $date . ' ' . $endTime,
            'status' => 'system',
            'target_group_id' => null,
            'venue' => 'Main Sanctuary',
            'expected_attendance' => null,
            'budget_total' => 0,
            'target_group' => 'All Church',
            'is_system' => true,
            'is_editable' => false,
            'system_notes' => $notes,
        ];
    }

    private function sundaysInMonth(string $monthStart): array
    {
        $cursor = strtotime(date('Y-m-01', strtotime($monthStart)));
        $month = date('m', $cursor);
        $sundays = [];

        while (date('m', $cursor) === $month) {
            if ((int) date('w', $cursor) === 0) {
                $sundays[] = date('Y-m-d', $cursor);
            }
            $cursor = strtotime('+1 day', $cursor);
        }

        return $sundays;
    }

    /* ══════════════════════════════════════════════
       Users CRUD
       ══════════════════════════════════════════════ */

    /** List all users with role details */
    public function listAllUsers(): void
    {
        try {
            $rows = $this->pdo->query(
                'SELECT u.id, u.full_name, u.email, u.phone, u.is_active,
                        u.last_login_at, u.created_at, u.role_id,
                        r.name AS role_name
                 FROM users u
                 LEFT JOIN roles r ON r.id = u.role_id
                 ORDER BY u.full_name ASC'
            )->fetchAll();
            Response::json(['success' => true, 'message' => 'All users', 'data' => $rows]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /** Create a new user */
    public function createUser(array $input): void
    {
        $required = ['full_name', 'phone', 'password', 'role_id'];
        foreach ($required as $f) {
            if (!isset($input[$f]) || trim((string) $input[$f]) === '') {
                Response::json(['success' => false, 'message' => "$f is required"], 422);
                return;
            }
        }

        $fullName = trim((string) $input['full_name']);
        $email    = trim((string) ($input['email'] ?? ''));
        $phone    = trim((string) $input['phone']);
        $roleId   = (int) $input['role_id'];
        $password = (string) $input['password'];

        if (mb_strlen($password) < 8) {
            Response::json(['success' => false, 'message' => 'Password must be at least 8 characters'], 422);
            return;
        }

        // Check duplicate phone
        $chk = $this->pdo->prepare('SELECT id FROM users WHERE phone = :phone LIMIT 1');
        $chk->execute([':phone' => $phone]);
        if ($chk->fetch()) {
            Response::json(['success' => false, 'message' => 'Phone number already in use'], 409);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (full_name, email, phone, password_hash, role_id, is_active, created_at, updated_at)
             VALUES (:name, :email, :phone, :hash, :role, 1, NOW(), NOW())'
        );
        $stmt->execute([
            ':name'  => $fullName,
            ':email' => $email,
            ':phone' => $phone,
            ':hash'  => $hash,
            ':role'  => $roleId,
        ]);

        $userId = (int) $this->pdo->lastInsertId();
        Audit::log($this->pdo, (int) ($_SESSION['user']['id'] ?? 0), 'settings', 'create_user', 'users', $userId, null, ['name' => $fullName], "User $fullName created");

        Response::json(['success' => true, 'message' => 'User created', 'data' => ['id' => $userId]]);
    }

    /** Update an existing user */
    public function updateUser(int $id, array $input): void
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            Response::json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $sets = [];
        $params = [':id' => $id];

        if (isset($input['full_name']) && trim((string) $input['full_name']) !== '') {
            $sets[] = 'full_name = :name';
            $params[':name'] = trim((string) $input['full_name']);
        }
        if (array_key_exists('email', $input)) {
            $sets[] = 'email = :email';
            $params[':email'] = trim((string) ($input['email'] ?? ''));
        }
        if (isset($input['phone']) && trim((string) $input['phone']) !== '') {
            // Check duplicate phone
            $chk = $this->pdo->prepare('SELECT id FROM users WHERE phone = :phone AND id != :uid LIMIT 1');
            $chk->execute([':phone' => trim((string) $input['phone']), ':uid' => $id]);
            if ($chk->fetch()) {
                Response::json(['success' => false, 'message' => 'Phone number already in use'], 409);
                return;
            }
            $sets[] = 'phone = :phone';
            $params[':phone'] = trim((string) $input['phone']);
        }
        if (isset($input['role_id'])) {
            $sets[] = 'role_id = :role';
            $params[':role'] = (int) $input['role_id'];
        }
        if (isset($input['is_active'])) {
            $sets[] = 'is_active = :active';
            $params[':active'] = (int) $input['is_active'];
        }
        if (isset($input['password']) && trim((string) $input['password']) !== '') {
            if (mb_strlen((string) $input['password']) < 8) {
                Response::json(['success' => false, 'message' => 'Password must be at least 8 characters'], 422);
                return;
            }
            $sets[] = 'password_hash = :hash';
            $params[':hash'] = password_hash((string) $input['password'], PASSWORD_DEFAULT);
        }

        if (empty($sets)) {
            Response::json(['success' => false, 'message' => 'No fields to update'], 422);
            return;
        }

        $sets[] = 'updated_at = NOW()';
        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $this->pdo->prepare($sql)->execute($params);

        Audit::log($this->pdo, (int) ($_SESSION['user']['id'] ?? 0), 'settings', 'update_user', 'users', $id, null, $input, "User #$id updated");

        Response::json(['success' => true, 'message' => 'User updated']);
    }

    /** Deactivate (soft-delete) a user */
    public function deleteUser(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT id, full_name FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $this->pdo->prepare('UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = :id')->execute([':id' => $id]);
        Audit::log($this->pdo, (int) ($_SESSION['user']['id'] ?? 0), 'settings', 'deactivate_user', 'users', $id, null, null, "User {$user['full_name']} deactivated");

        Response::json(['success' => true, 'message' => 'User deactivated']);
    }

    /* ══════════════════════════════════════════════
       Church Profile (key-value settings)
       ══════════════════════════════════════════════ */

    /** Get all church profile settings */
    public function getChurchProfile(): void
    {
        try {
            // Check if church_settings table exists
            if (!$this->tableExists('church_settings')) {
                Response::json(['success' => true, 'message' => 'Church profile', 'data' => []], 200);
                return;
            }
            
            $rows = $this->pdo->query('SELECT setting_key, setting_value FROM church_settings ORDER BY id ASC')->fetchAll();
            $profile = [];
            foreach ($rows as $r) {
                $profile[$r['setting_key']] = $r['setting_value'];
            }
            Response::json(['success' => true, 'message' => 'Church profile', 'data' => $profile]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /** Update church profile settings */
    public function updateChurchProfile(array $input): void
    {
        try {
            // Check if church_settings table exists
            if (!$this->tableExists('church_settings')) {
                Response::json(['success' => false, 'message' => 'Database migration required. Run database/migrations/2026_04_03_001_create_church_settings.sql'], 400);
                return;
            }
            
            $allowed = ['church_name', 'location', 'phone', 'email', 'address', 'pastor_name', 'founded_year', 'church_logo'];
            $stmt = $this->pdo->prepare(
                'INSERT INTO church_settings (setting_key, setting_value)
                 VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );

            $updated = 0;
            foreach ($input as $key => $value) {
                if (!in_array($key, $allowed, true)) continue;
                $stmt->execute([':k' => $key, ':v' => trim((string) $value)]);
                $updated++;
            }

            Audit::log($this->pdo, (int) ($_SESSION['user']['id'] ?? 0), 'settings', 'update_church_profile', 'church_settings', 0, null, $input, "Church profile updated");

            Response::json(['success' => true, 'message' => 'Church profile updated', 'data' => ['updated' => $updated]]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /** Upload church logo image */
    public function uploadChurchLogo(): void
    {
        if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'No file uploaded or upload error'], 422);
            return;
        }

        $file = $_FILES['logo'];
        $maxSize = 2 * 1024 * 1024; // 2 MB
        if ($file['size'] > $maxSize) {
            Response::json(['success' => false, 'message' => 'File too large (max 2 MB)'], 422);
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];
        if (!isset($allowedMimes[$mime])) {
            Response::json(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP, SVG'], 422);
            return;
        }

        $ext = $allowedMimes[$mime];
        $filename = 'church_logo_' . time() . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/logos/';
        $destPath = $uploadDir . $filename;

        // Delete old logo file if exists
        $oldLogo = $this->pdo->query("SELECT setting_value FROM church_settings WHERE setting_key = 'church_logo'")->fetchColumn();
        if ($oldLogo && $oldLogo !== '') {
            $oldFile = __DIR__ . '/../../public' . $oldLogo;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Response::json(['success' => false, 'message' => 'Failed to save uploaded file'], 500);
            return;
        }

        $relativePath = '/uploads/logos/' . $filename;
        $stmt = $this->pdo->prepare(
            'INSERT INTO church_settings (setting_key, setting_value)
             VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([':k' => 'church_logo', ':v' => $relativePath]);

        Audit::log($this->pdo, (int) ($_SESSION['user']['id'] ?? 0), 'settings', 'upload_church_logo', 'church_settings', 0, null, ['logo' => $relativePath], 'Church logo uploaded');

        Response::json(['success' => true, 'message' => 'Logo uploaded successfully', 'data' => ['logo_url' => $relativePath]]);
    }

    /** Delete church logo (file + DB) */
    public function deleteChurchLogo(): void
    {
        $oldLogo = $this->pdo->query("SELECT setting_value FROM church_settings WHERE setting_key = 'church_logo'")->fetchColumn();
        if ($oldLogo && $oldLogo !== '') {
            $oldFile = __DIR__ . '/../../public' . $oldLogo;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO church_settings (setting_key, setting_value)
             VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([':k' => 'church_logo', ':v' => '']);

        Audit::log($this->pdo, (int) ($_SESSION['user']['id'] ?? 0), 'settings', 'delete_church_logo', 'church_settings', 0, ['logo' => $oldLogo], null, 'Church logo removed');

        Response::json(['success' => true, 'message' => 'Logo removed successfully']);
    }

    /* ══════════════════════════════════════════════
       Communication / Messaging
       ══════════════════════════════════════════════ */

    /** Send a broadcast message (SMS) to selected recipients */
    public function sendMessage(array $input): void
    {
        $user = Auth::user();
        $actorId = isset($user['id']) ? (int) $user['id'] : null;
        if ($actorId === null) {
            Response::json(['success' => false, 'message' => 'Not authenticated'], 401);
            return;
        }
        if (!Auth::can('communication.send')) {
            Response::json(['success' => false, 'message' => 'No permission to send messages'], 403); return;
        }
        $recipientType = trim((string) ($input['recipient_type'] ?? 'all'));
        $recipientIds = $input['recipient_ids'] ?? [];

        if ($messageText === '') {
            Response::json(['success' => false, 'message' => 'Message is required'], 422);
            return;
        }
        if (mb_strlen($messageText) > 480) {
            Response::json(['success' => false, 'message' => 'Message must be 480 characters or fewer'], 422);
            return;
        }
        if (!in_array($recipientType, ['all', 'members', 'groups'], true)) {
            Response::json(['success' => false, 'message' => 'Invalid recipient_type'], 422);
            return;
        }
        if ($recipientType !== 'all' && empty($recipientIds)) {
            Response::json(['success' => false, 'message' => 'Select at least one recipient'], 422);
            return;
        }

        // Resolve phone numbers
        $phones = []; // [{member_id, phone, group_id?}]

        if ($recipientType === 'all') {
            $rows = $this->pdo->query(
                "SELECT id, phone FROM members WHERE member_status = 'active' AND phone IS NOT NULL AND phone != '' ORDER BY id"
            )->fetchAll();
            foreach ($rows as $r) {
                $phones[] = ['member_id' => (int) $r['id'], 'phone' => $r['phone'], 'group_id' => null];
            }
        } elseif ($recipientType === 'members') {
            $ids = array_map('intval', (array) $recipientIds);
            if (empty($ids)) {
                Response::json(['success' => false, 'message' => 'No members selected'], 422);
                return;
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT id, phone FROM members WHERE id IN ($placeholders) AND phone IS NOT NULL AND phone != ''"
            );
            $stmt->execute($ids);
            foreach ($stmt->fetchAll() as $r) {
                $phones[] = ['member_id' => (int) $r['id'], 'phone' => $r['phone'], 'group_id' => null];
            }
        } elseif ($recipientType === 'groups') {
            $ids = array_map('intval', (array) $recipientIds);
            if (empty($ids)) {
                Response::json(['success' => false, 'message' => 'No groups selected'], 422);
                return;
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT m.id, m.phone, mga.group_id
                 FROM member_group_assignments mga
                 INNER JOIN members m ON m.id = mga.member_id
                 WHERE mga.group_id IN ($placeholders)
                   AND mga.ended_at IS NULL
                   AND m.member_status = 'active'
                   AND m.phone IS NOT NULL AND m.phone != ''
                 GROUP BY m.id, mga.group_id"
            );
            $stmt->execute($ids);
            foreach ($stmt->fetchAll() as $r) {
                $phones[] = ['member_id' => (int) $r['id'], 'phone' => $r['phone'], 'group_id' => (int) $r['group_id']];
            }
        }

        // Deduplicate by phone number
        $seen = [];
        $uniquePhones = [];
        foreach ($phones as $p) {
            if (isset($seen[$p['phone']])) continue;
            $seen[$p['phone']] = true;
            $uniquePhones[] = $p;
        }

        if (empty($uniquePhones)) {
            Response::json(['success' => false, 'message' => 'No recipients with valid phone numbers found'], 422);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            // Create message record
            $msgStmt = $this->pdo->prepare(
                'INSERT INTO messages (message_text, recipient_type, recipient_ids, recipient_count, channel, status, sent_by, created_at)
                 VALUES (:msg, :rtype, :rids, :rcount, :channel, :status, :sent_by, NOW())'
            );
            $msgStmt->execute([
                ':msg'     => $messageText,
                ':rtype'   => $recipientType,
                ':rids'    => json_encode(array_map('intval', (array) $recipientIds)),
                ':rcount'  => count($uniquePhones),
                ':channel' => 'sms',
                ':status'  => 'sending',
                ':sent_by' => $actorId,
            ]);
            $messageId = (int) $this->pdo->lastInsertId();

            // Insert individual SMS logs
            $smsStmt = $this->pdo->prepare(
                'INSERT INTO sms_logs (message_id, recipient_type, group_id, member_id, phone, message_text, message_type, provider, delivery_status, sent_by, sent_at)
                 VALUES (:mid, :rtype, :gid, :member_id, :phone, :msg, :mtype, :provider, :status, :sent_by, NOW())'
            );

            $sentCount = 0;
            $failedCount = 0;

            foreach ($uniquePhones as $recipient) {
                // Abstract SMS provider call
                $deliveryResult = $this->dispatchSms($recipient['phone'], $messageText);

                $smsStmt->execute([
                    ':mid'       => $messageId,
                    ':rtype'     => $recipientType === 'all' ? 'member' : ($recipientType === 'groups' ? 'group' : 'member'),
                    ':gid'       => $recipient['group_id'],
                    ':member_id' => $recipient['member_id'],
                    ':phone'     => $recipient['phone'],
                    ':msg'       => $messageText,
                    ':mtype'     => 'broadcast',
                    ':provider'  => $deliveryResult['provider'],
                    ':status'    => $deliveryResult['status'],
                    ':sent_by'   => $actorId,
                ]);

                if ($deliveryResult['status'] === 'failed') {
                    $failedCount++;
                } else {
                    $sentCount++;
                }
            }

            // Update message record with final counts
            $finalStatus = $failedCount === 0 ? 'sent' : ($sentCount === 0 ? 'failed' : 'partial');
            $this->pdo->prepare(
                'UPDATE messages SET sent_count = :sent, failed_count = :failed, status = :status, updated_at = NOW() WHERE id = :id'
            )->execute([
                ':sent'   => $sentCount,
                ':failed' => $failedCount,
                ':status' => $finalStatus,
                ':id'     => $messageId,
            ]);

            $this->pdo->commit();

            Audit::log($this->pdo, $actorId, 'communication', 'send_message', 'messages', $messageId, null,
                ['recipients' => count($uniquePhones), 'sent' => $sentCount, 'failed' => $failedCount],
                "Broadcast to " . count($uniquePhones) . " recipients"
            );

            Response::json([
                'success' => true,
                'message' => "Message sent to $sentCount of " . count($uniquePhones) . " recipients" . ($failedCount > 0 ? " ($failedCount failed)" : ''),
                'data' => [
                    'message_id' => $messageId,
                    'total'      => count($uniquePhones),
                    'sent'       => $sentCount,
                    'failed'     => $failedCount,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Response::json(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }

    /** Abstract SMS provider dispatch — replace with real API integration */
    private function dispatchSms(string $phone, string $message): array
    {
        // ──────────────────────────────────────────────────
        // SMS Provider Abstraction Layer
        // Replace this method body with actual API calls to:
        //   - Beem Africa: https://docs.beem.africa/
        //   - Africa's Talking: https://africastalking.com/
        //   - Twilio:  https://www.twilio.com/docs/sms
        // ──────────────────────────────────────────────────
        // For now, simulate successful delivery:
        return [
            'provider'    => 'internal',
            'status'      => 'sent',
            'provider_id' => null,
        ];
    }

    /** List message history (broadcasts) */
    public function listMessages(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $total = (int) $this->pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();

        $stmt = $this->pdo->prepare(
            'SELECT m.*, u.full_name AS sent_by_name
             FROM messages m
             LEFT JOIN users u ON u.id = m.sent_by
             ORDER BY m.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        Response::json([
            'success' => true,
            'message' => 'Message history',
            'data'    => $rows,
            'meta'    => ['total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $limit)],
        ]);
    }

    /** Get message detail with individual SMS delivery statuses */
    public function getMessageDetail(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, u.full_name AS sent_by_name
             FROM messages m
             LEFT JOIN users u ON u.id = m.sent_by
             WHERE m.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $msg = $stmt->fetch();
        if (!$msg) {
            Response::json(['success' => false, 'message' => 'Message not found'], 404);
            return;
        }

        $logsStmt = $this->pdo->prepare(
            'SELECT sl.id, sl.phone, sl.delivery_status, sl.sent_at,
                    mb.first_name, mb.last_name,
                    g.name AS group_name
             FROM sms_logs sl
             LEFT JOIN members mb ON mb.id = sl.member_id
             LEFT JOIN `groups` g ON g.id = sl.group_id
             WHERE sl.message_id = :mid
             ORDER BY sl.id ASC'
        );
        $logsStmt->execute([':mid' => $id]);
        $logs = $logsStmt->fetchAll();

        $msg['recipients'] = $logs;
        Response::json(['success' => true, 'message' => 'Message detail', 'data' => $msg]);
    }

    /** List members with phone for recipient picker */
    public function listMembersForMessaging(): void
    {
        $rows = $this->pdo->query(
            "SELECT id, first_name, last_name, phone
             FROM members
             WHERE member_status = 'active' AND phone IS NOT NULL AND phone != ''
             ORDER BY first_name, last_name"
        )->fetchAll();
        Response::json(['success' => true, 'message' => 'Members for messaging', 'data' => $rows]);
    }

    /** List groups with member count for recipient picker */
    public function listGroupsForMessaging(): void
    {
        $rows = $this->pdo->query(
            "SELECT g.id, g.name,
                    COUNT(DISTINCT mga.member_id) AS member_count
             FROM `groups` g
             LEFT JOIN member_group_assignments mga ON mga.group_id = g.id AND mga.ended_at IS NULL
             WHERE g.is_active = 1
             GROUP BY g.id, g.name
             ORDER BY g.name"
        )->fetchAll();
        Response::json(['success' => true, 'message' => 'Groups for messaging', 'data' => $rows]);
    }

    /* ───── Sadaka ───── */

    /** Get monthly sadaka overview stats */
    public function sadakaOverview(): void
    {
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            Response::json(['success' => false, 'message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        // Total amount for the month
        $totalStmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(amount), 0) as total_amount
             FROM sadaka_entries
             WHERE entry_date BETWEEN :start AND :end'
        );
        $totalStmt->execute([':start' => $monthStart, ':end' => $monthEnd]);
        $totalAmount = (float) $totalStmt->fetchColumn();

        // Count of unique contributors
        $contributorsStmt = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT member_id) as contributor_count
             FROM sadaka_entries
             WHERE entry_date BETWEEN :start AND :end'
        );
        $contributorsStmt->execute([':start' => $monthStart, ':end' => $monthEnd]);
        $contributorCount = (int) $contributorsStmt->fetchColumn();

        // Average per contributor
        $averagePerContributor = $contributorCount > 0 ? $totalAmount / $contributorCount : 0;

        Response::json([
            'success' => true,
            'message' => 'Sadaka overview',
            'data' => [
                'total_amount' => $totalAmount,
                'contributor_count' => $contributorCount,
                'average_per_contributor' => round($averagePerContributor, 2),
                'month' => $month,
            ],
        ]);
    }

    /** Get sadaka entries for a month, optionally filtered by type */
    public function sadakaEntries(): void
    {
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        $type  = trim((string) ($_GET['type'] ?? 'all'));

        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            Response::json(['success' => false, 'message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $query = 'SELECT se.id, se.entry_date, se.sadaka_type, se.member_id,
                         se.amount, se.payment_method, se.description, se.reference_no,
                         CONCAT(m.first_name, " ", m.last_name) as member_name,
                         m.phone, se.created_at
                  FROM sadaka_entries se
                  INNER JOIN members m ON m.id = se.member_id
                  WHERE se.entry_date BETWEEN :start AND :end';

        $params = [':start' => $monthStart, ':end' => $monthEnd];

        if ($type !== 'all') {
            $query .= ' AND se.sadaka_type = :type';
            $params[':type'] = $type;
        }

        $query .= ' ORDER BY se.entry_date DESC, m.first_name, m.last_name';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();

        // Group by week
        $entriesByWeek = [];
        foreach ($entries as $entry) {
            $entryDate = new \DateTime($entry['entry_date']);
            $dayOfWeek = (int) $entryDate->format('w'); // 0=Sunday, 6=Saturday
            $weekStart = (clone $entryDate)->modify((-$dayOfWeek) . ' days')->format('Y-m-d');
            $weekNum   = (int) ceil((int) $entryDate->format('d') / 7);

            if (!isset($entriesByWeek[$weekNum])) {
                $entriesByWeek[$weekNum] = [];
            }
            $entriesByWeek[$weekNum][] = $entry;
        }

        Response::json([
            'success' => true,
            'message' => 'Sadaka entries',
            'data' => [
                'entries' => $entries,
                'entries_by_week' => $entriesByWeek,
                'month' => $month,
            ],
        ]);
    }

    /** Create a new sadaka entry */
    public function sadakaCreate(): void
    {
        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;

        $entryDate   = trim((string) ($input['entry_date'] ?? ''));
        $sadakaType  = trim((string) ($input['sadaka_type'] ?? 'sadaka_upendo'));
        $memberId    = (int) ($input['member_id'] ?? 0);
        $amount      = (float) ($input['amount'] ?? 0);
        $paymentMethod = trim((string) ($input['payment_method'] ?? 'cash'));
        $description = trim((string) ($input['description'] ?? ''));
        $referenceNo = trim((string) ($input['reference_no'] ?? ''));

        // Validation
        if (!$entryDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
            Response::json(['success' => false, 'message' => 'Valid entry date is required'], 422);
        }
        if ($memberId <= 0) {
            Response::json(['success' => false, 'message' => 'Valid member is required'], 422);
        }
        if ($amount <= 0) {
            Response::json(['success' => false, 'message' => 'Amount must be greater than 0'], 422);
        }

        $validTypes = ['sadaka_upendo', 'sadaka_maendeleo', 'mafungu_kumi', 'machangizo'];
        if (!in_array($sadakaType, $validTypes, true)) {
            Response::json(['success' => false, 'message' => 'Invalid sadaka type'], 422);
        }

        $validMethods = ['cash', 'mobile_money', 'bank_transfer', 'cheque', 'other'];
        if (!in_array($paymentMethod, $validMethods, true)) {
            Response::json(['success' => false, 'message' => 'Invalid payment method'], 422);
        }

        // Check member exists
        $memberStmt = $this->pdo->prepare('SELECT id FROM members WHERE id = :id LIMIT 1');
        $memberStmt->execute([':id' => $memberId]);
        if (!$memberStmt->fetch()) {
            Response::json(['success' => false, 'message' => 'Member not found'], 404);
        }

        $userId = Auth::user()['id'] ?? null;

        // Insert sadaka entry
        $insertStmt = $this->pdo->prepare(
            'INSERT INTO sadaka_entries
             (entry_date, sadaka_type, member_id, amount, payment_method, description, reference_no, recorded_by, created_at)
             VALUES (:entry_date, :sadaka_type, :member_id, :amount, :payment_method, :description, :reference_no, :recorded_by, NOW())'
        );

        try {
            $insertStmt->execute([
                ':entry_date' => $entryDate,
                ':sadaka_type' => $sadakaType,
                ':member_id' => $memberId,
                ':amount' => $amount,
                ':payment_method' => $paymentMethod,
                ':description' => $description,
                ':reference_no' => $referenceNo,
                ':recorded_by' => $userId,
            ]);

            $entryId = (int) $this->pdo->lastInsertId();

            Audit::log($this->pdo, (int) $userId, 'sadaka', 'create', 'sadaka_entries', $entryId, null, ['amount' => $amount, 'type' => $sadakaType], 'Created sadaka entry');

            Response::json([
                'success' => true,
                'message' => 'Sadaka entry created successfully',
                'data' => ['id' => $entryId],
            ], 201);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Failed to create entry: ' . $e->getMessage()], 500);
        }
    }

    /** Update an existing sadaka entry */
    public function sadakaUpdate(): void
    {
        $entryId = (int) ($_GET['id'] ?? 0);
        if ($entryId <= 0) {
            Response::json(['success' => false, 'message' => 'Valid entry ID is required'], 422);
        }

        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;

        // Check entry exists
        $checkStmt = $this->pdo->prepare('SELECT id FROM sadaka_entries WHERE id = :id LIMIT 1');
        $checkStmt->execute([':id' => $entryId]);
        if (!$checkStmt->fetch()) {
            Response::json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        // Build update query dynamically based on provided fields
        $updates = [];
        $params = [':id' => $entryId];

        if (isset($input['entry_date'])) {
            $entryDate = trim((string) $input['entry_date']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
                Response::json(['success' => false, 'message' => 'Invalid entry date'], 422);
            }
            $updates[] = 'entry_date = :entry_date';
            $params[':entry_date'] = $entryDate;
        }

        if (isset($input['sadaka_type'])) {
            $type = trim((string) $input['sadaka_type']);
            $validTypes = ['sadaka_upendo', 'sadaka_maendeleo', 'mafungu_kumi', 'machangizo'];
            if (!in_array($type, $validTypes, true)) {
                Response::json(['success' => false, 'message' => 'Invalid sadaka type'], 422);
            }
            $updates[] = 'sadaka_type = :sadaka_type';
            $params[':sadaka_type'] = $type;
        }

        if (isset($input['amount'])) {
            $amount = (float) $input['amount'];
            if ($amount <= 0) {
                Response::json(['success' => false, 'message' => 'Amount must be greater than 0'], 422);
            }
            $updates[] = 'amount = :amount';
            $params[':amount'] = $amount;
        }

        if (isset($input['payment_method'])) {
            $method = trim((string) $input['payment_method']);
            $validMethods = ['cash', 'mobile_money', 'bank_transfer', 'cheque', 'other'];
            if (!in_array($method, $validMethods, true)) {
                Response::json(['success' => false, 'message' => 'Invalid payment method'], 422);
            }
            $updates[] = 'payment_method = :payment_method';
            $params[':payment_method'] = $method;
        }

        if (isset($input['description'])) {
            $updates[] = 'description = :description';
            $params[':description'] = trim((string) $input['description']);
        }

        if (isset($input['reference_no'])) {
            $updates[] = 'reference_no = :reference_no';
            $params[':reference_no'] = trim((string) $input['reference_no']);
        }

        if (empty($updates)) {
            Response::json(['success' => false, 'message' => 'No fields to update'], 422);
        }

        $updates[] = 'updated_at = NOW()';
        $updateQuery = 'UPDATE sadaka_entries SET ' . implode(', ', $updates) . ' WHERE id = :id';

        try {
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute($params);

            $userId = Auth::user()['id'] ?? null;
            Audit::log($this->pdo, (int) $userId, 'sadaka', 'update', 'sadaka_entries', $entryId, null, $input, 'Updated sadaka entry');

            Response::json(['success' => true, 'message' => 'Sadaka entry updated successfully']);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Failed to update entry: ' . $e->getMessage()], 500);
        }
    }

    /** Delete a sadaka entry */
    public function sadakaDelete(): void
    {
        $entryId = (int) ($_GET['id'] ?? 0);
        if ($entryId <= 0) {
            Response::json(['success' => false, 'message' => 'Valid entry ID is required'], 422);
        }

        // Check entry exists
        $checkStmt = $this->pdo->prepare('SELECT id FROM sadaka_entries WHERE id = :id LIMIT 1');
        $checkStmt->execute([':id' => $entryId]);
        if (!$checkStmt->fetch()) {
            Response::json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        try {
            $deleteStmt = $this->pdo->prepare('DELETE FROM sadaka_entries WHERE id = :id');
            $deleteStmt->execute([':id' => $entryId]);

            $userId = Auth::user()['id'] ?? null;
            Audit::log($this->pdo, (int) $userId, 'sadaka', 'delete', 'sadaka_entries', $entryId, null, [], 'Deleted sadaka entry');

            Response::json(['success' => true, 'message' => 'Sadaka entry deleted successfully']);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Failed to delete entry: ' . $e->getMessage()], 500);
        }
    }

    /** Get details of a single sadaka entry */
    public function sadakaGetEntry(): void
    {
        $entryId = (int) ($_GET['id'] ?? 0);
        if ($entryId <= 0) {
            Response::json(['success' => false, 'message' => 'Valid entry ID is required'], 422);
        }

        $stmt = $this->pdo->prepare(
            'SELECT se.id, se.entry_date, se.sadaka_type, se.member_id,
                    se.amount, se.payment_method, se.description, se.reference_no,
                    CONCAT(m.first_name, " ", m.last_name) as member_name,
                    se.created_at, se.updated_at
             FROM sadaka_entries se
             INNER JOIN members m ON m.id = se.member_id
             WHERE se.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $entryId]);
        $entry = $stmt->fetch();

        if (!$entry) {
            Response::json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        Response::json(['success' => true, 'message' => 'Sadaka entry', 'data' => $entry]);
    }

    /** Get per-member sadaka summary for a month */
    public function sadakaMemberReport(): void
    {
        $month = trim((string) ($_GET['month'] ?? date('Y-m')));
        $type  = trim((string) ($_GET['type'] ?? 'all'));

        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            Response::json(['success' => false, 'message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $query = 'SELECT m.id as member_id, m.member_code,
                         CONCAT(m.first_name, " ", m.last_name) as member_name,
                         m.phone,
                         COUNT(se.id) as entry_count,
                         COALESCE(SUM(se.amount), 0) as total_amount,
                         GROUP_CONCAT(DISTINCT se.sadaka_type) as types_contributed
                  FROM members m
                  LEFT JOIN sadaka_entries se ON se.member_id = m.id
                    AND se.entry_date BETWEEN :start AND :end';

        $params = [':start' => $monthStart, ':end' => $monthEnd];

        if ($type !== 'all') {
            $query .= ' AND se.sadaka_type = :type';
            $params[':type'] = $type;
        }

        $query .= ' WHERE m.member_status = "active"
                  GROUP BY m.id, m.member_code, m.first_name, m.last_name, m.phone
                  ORDER BY total_amount DESC, m.first_name, m.last_name';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $report = $stmt->fetchAll();

        Response::json([
            'success' => true,
            'message' => 'Sadaka member report',
            'data' => [
                'report' => $report,
                'month' => $month,
                'type' => $type,
            ],
        ]);
    }

    /** Bulk import sadaka entries from CSV */
    public function sadakaImport(): void
    {
        if (!isset($_FILES['file'])) {
            Response::json(['success' => false, 'message' => 'CSV file is required'], 422);
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'File upload error'], 422);
        }

        // Validate file is CSV
        if (!in_array($file['type'], ['text/csv', 'application/vnd.ms-excel', 'text/plain'], true)) {
            Response::json(['success' => false, 'message' => 'File must be a CSV file'], 422);
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Response::json(['success' => false, 'message' => 'Could not read file'], 500);
        }

        $successCount = 0;
        $errors = [];
        $lineNum = 0;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNum++;
                if ($lineNum === 1) continue; // Skip header row

                // Expected format: entry_date, sadaka_type, member_id, amount, payment_method, description
                if (count($row) < 4) {
                    $errors[] = "Line $lineNum: Insufficient columns";
                    continue;
                }

                $entryDate = trim($row[0]);
                $sadakaType = trim($row[1]);
                $memberId = (int) trim($row[2]);
                $amount = (float) trim($row[3]);
                $paymentMethod = trim($row[4] ?? 'cash');
                $description = trim($row[5] ?? '');

                // Validate
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
                    $errors[] = "Line $lineNum: Invalid date format";
                    continue;
                }

                if (!in_array($sadakaType, ['sadaka_upendo', 'sadaka_maendeleo', 'mafungu_kumi', 'machangizo'], true)) {
                    $errors[] = "Line $lineNum: Invalid sadaka type";
                    continue;
                }

                if ($amount <= 0) {
                    $errors[] = "Line $lineNum: Invalid amount";
                    continue;
                }

                // Check member exists
                $memberStmt = $this->pdo->prepare('SELECT id FROM members WHERE id = :id LIMIT 1');
                $memberStmt->execute([':id' => $memberId]);
                if (!$memberStmt->fetch()) {
                    $errors[] = "Line $lineNum: Member ID $memberId not found";
                    continue;
                }

                // Insert
                $insertStmt = $this->pdo->prepare(
                    'INSERT INTO sadaka_entries
                     (entry_date, sadaka_type, member_id, amount, payment_method, description, recorded_by, created_at)
                     VALUES (:entry_date, :sadaka_type, :member_id, :amount, :payment_method, :description, :recorded_by, NOW())'
                );

                $userId = Auth::user()['id'] ?? null;
                $insertStmt->execute([
                    ':entry_date' => $entryDate,
                    ':sadaka_type' => $sadakaType,
                    ':member_id' => $memberId,
                    ':amount' => $amount,
                    ':payment_method' => $paymentMethod,
                    ':description' => $description,
                    ':recorded_by' => $userId,
                ]);

                $successCount++;
            }

            fclose($handle);

            Response::json([
                'success' => count($errors) === 0,
                'message' => "Import completed: $successCount entries imported",
                'data' => [
                    'imported_count' => $successCount,
                    'errors' => $errors,
                    'error_count' => count($errors),
                ],
            ]);
        } catch (\Throwable $e) {
            fclose($handle);
            Response::json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    /** Search members by name for dropdown/autocomplete */
    public function memberSearch(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));

        if (mb_strlen($query) < 2) {
            Response::json(['success' => true, 'message' => 'Members search', 'data' => []]);
        }

        $searchTerm = '%' . $query . '%';

        $stmt = $this->pdo->prepare(
            'SELECT id, member_code, CONCAT(first_name, " ", last_name) as name, phone
             FROM members
             WHERE member_status = "active" AND (first_name LIKE :term OR last_name LIKE :term OR member_code LIKE :term)
             ORDER BY first_name, last_name
             LIMIT 20'
        );

        $stmt->execute([':term' => $searchTerm]);
        $members = $stmt->fetchAll();

        Response::json(['success' => true, 'message' => 'Members search', 'data' => $members]);
    }
}
