<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Response;
use PDO;
use Exception;

final class SadakaController
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Get all sadaka categories
     */
    public function getCategories(): void
    {
        if (!Auth::can('finance.view')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, category_name, category_description, category_slug, is_active 
                 FROM sadaka_categories 
                 WHERE is_active = 1 
                 ORDER BY category_name ASC'
            );
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::json(['success' => true, 'data' => $categories]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error fetching categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get sadaka entries for a specific category with members data
     */
    public function getEntriesByCategory(string $categorySlug, ?string $month = null, ?string $year = null): void
    {
        if (!Auth::can('finance.view')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $currentYear = (int) ($year ?? date('Y'));
            $currentMonth = (int) ($month ?? date('m'));

            // Validate month and year ranges
            if ($currentMonth < 1 || $currentMonth > 12) {
                Response::json(['success' => false, 'message' => 'Invalid month. Must be between 1 and 12'], 422);
                return;
            }
            if ($currentYear < 2000 || $currentYear > 2100) {
                Response::json(['success' => false, 'message' => 'Invalid year. Must be between 2000 and 2100'], 422);
                return;
            }

            // Get category ID from slug
            $stmt = $this->pdo->prepare('SELECT id FROM sadaka_categories WHERE category_slug = ?');
            $stmt->execute([$categorySlug]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                Response::json(['success' => false, 'message' => 'Category not found'], 404);
                return;
            }

            $categoryId = $category['id'];

            // Get all active members with their sadaka entries for the month
            $sql = "
                SELECT 
                    m.id,
                    m.member_code,
                    m.first_name,
                    m.last_name,
                    COALESCE(SUM(se.amount), 0) as month_total,
                    GROUP_CONCAT(DISTINCT se.entry_week) as weeks_with_entries
                FROM members m
                LEFT JOIN sadaka_entries se ON m.id = se.member_id 
                    AND se.category_id = ?
                    AND se.entry_month = ?
                    AND se.entry_year = ?
                WHERE m.member_status = 'active'
                GROUP BY m.id
                ORDER BY m.last_name, m.first_name
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId, $currentMonth, $currentYear]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get week-by-week data for each member
            $weekData = [];
            foreach ($members as &$member) {
                $weekStmt = $this->pdo->prepare(
                    'SELECT entry_week, SUM(amount) as week_total 
                     FROM sadaka_entries 
                     WHERE member_id = ? AND category_id = ? AND entry_month = ? AND entry_year = ? AND entry_week IS NOT NULL
                     GROUP BY entry_week
                     ORDER BY entry_week'
                );
                $weekStmt->execute([$member['id'], $categoryId, $currentMonth, $currentYear]);
                $member['week_data'] = $weekStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch individual detailed entries for this member
                $entriesStmt = $this->pdo->prepare(
                    'SELECT id, entry_date, entry_week, amount, notes 
                     FROM sadaka_entries 
                     WHERE member_id = ? AND category_id = ? AND entry_month = ? AND entry_year = ?
                     ORDER BY entry_date DESC'
                );
                $entriesStmt->execute([$member['id'], $categoryId, $currentMonth, $currentYear]);
                $member['entries'] = $entriesStmt->fetchAll(PDO::FETCH_ASSOC);

                $member['yearly_total'] = 0;
            }

            // Get yearly total for each member (only if we have members)
            if (!empty($members)) {
                $memberIds = array_column($members, 'id');
                $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
                $yearlyStmt = $this->pdo->prepare(
                    "SELECT member_id, SUM(amount) as yearly_total 
                     FROM sadaka_entries 
                     WHERE member_id IN ($placeholders) 
                     AND category_id = ? AND entry_year = ?
                     GROUP BY member_id"
                );
                $params = array_merge($memberIds, [$categoryId, $currentYear]);
                $yearlyStmt->execute($params);
                $yearlyData = $yearlyStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                foreach ($members as &$member) {
                    $member['yearly_total'] = (float) ($yearlyData[$member['id']] ?? 0);
                }
            }

            Response::json([
                'success' => true,
                'data' => [
                    'category_id' => $categoryId,
                    'category_slug' => $categorySlug,
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'members' => $members
                ]
            ]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error fetching entries: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add a single sadaka entry
     */
    public function addEntry(array $input): void
    {
        if (!Auth::can('sadaka.create')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $memberId = (int) ($input['member_id'] ?? 0);
            $categoryId = (int) ($input['category_id'] ?? 0);
            $amount = (float) ($input['amount'] ?? 0);
            $entryDate = trim((string) ($input['entry_date'] ?? date('Y-m-d')));
            $week = isset($input['week']) ? (int) $input['week'] : null;
            $notes = trim((string) ($input['notes'] ?? ''));

            if ($memberId <= 0 || $categoryId <= 0 || $amount <= 0) {
                Response::json(['success' => false, 'message' => 'Invalid member, category, or amount'], 422);
                return;
            }

            // Validate amount precision
            if ($amount > 9999999.99) {
                Response::json(['success' => false, 'message' => 'Amount exceeds maximum allowed value (9,999,999.99)'], 422);
                return;
            }

            // Validate week if provided
            if ($week !== null && ($week < 1 || $week > 4)) {
                Response::json(['success' => false, 'message' => 'Week must be between 1 and 4'], 422);
                return;
            }

            if (!$this->validateDate($entryDate)) {
                Response::json(['success' => false, 'message' => 'Invalid date format'], 422);
                return;
            }

            $userId = Auth::user()['id'] ?? null;
            $month = (int) date('m', strtotime($entryDate));
            $year = (int) date('Y', strtotime($entryDate));

            $stmt = $this->pdo->prepare(
                'INSERT INTO sadaka_entries 
                (member_id, category_id, entry_month, entry_year, entry_week, amount, entry_date, notes, entered_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([$memberId, $categoryId, $month, $year, $week, $amount, $entryDate, $notes, $userId]);
            $entryId = (int) $this->pdo->lastInsertId();

            Audit::log(
                $this->pdo,
                $userId,
                'sadaka',
                'entry_created',
                'sadaka_entries',
                $entryId,
                null,
                ['member_id' => $memberId, 'category_id' => $categoryId, 'amount' => $amount],
                'Created sadaka entry'
            );

            Response::json(['success' => true, 'message' => 'Entry added successfully', 'id' => $this->pdo->lastInsertId()]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error adding entry: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing sadaka entry
     */
    public function updateEntry(int $entryId, array $input): void
    {
        if (!Auth::can('sadaka.create')) { // Reuse sadaka.create permission for editing
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $memberId = (int) ($input['member_id'] ?? 0);
            $amount = (float) ($input['amount'] ?? 0);
            $entryDate = trim((string) ($input['entry_date'] ?? date('Y-m-d')));
            $week = isset($input['week']) ? (int) $input['week'] : null;
            $notes = trim((string) ($input['notes'] ?? ''));

            if ($memberId <= 0 || $amount <= 0) {
                Response::json(['success' => false, 'message' => 'Invalid member or amount'], 422);
                return;
            }

            // Validate amount precision
            if ($amount > 9999999.99) {
                Response::json(['success' => false, 'message' => 'Amount exceeds maximum allowed value (9,999,999.99)'], 422);
                return;
            }

            // Validate week if provided
            if ($week !== null && ($week < 1 || $week > 4)) {
                Response::json(['success' => false, 'message' => 'Week must be between 1 and 4'], 422);
                return;
            }

            if (!$this->validateDate($entryDate)) {
                Response::json(['success' => false, 'message' => 'Invalid date format'], 422);
                return;
            }

            $userId = Auth::user()['id'] ?? null;
            $month = (int) date('m', strtotime($entryDate));
            $year = (int) date('Y', strtotime($entryDate));

            $stmt = $this->pdo->prepare(
                'UPDATE sadaka_entries 
                 SET member_id = ?, entry_month = ?, entry_year = ?, entry_week = ?, amount = ?, entry_date = ?, notes = ?
                 WHERE id = ?'
            );
            $stmt->execute([$memberId, $month, $year, $week, $amount, $entryDate, $notes, $entryId]);

            Audit::log(
                $this->pdo,
                $userId,
                'sadaka',
                'entry_updated',
                'sadaka_entries',
                $entryId,
                null,
                ['member_id' => $memberId, 'amount' => $amount],
                'Updated sadaka entry'
            );

            Response::json(['success' => true, 'message' => 'Entry updated successfully']);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error updating entry: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload sadaka entries from Excel file
     */
    public function uploadEntries(array $input, array $files): void
    {
        if (!Auth::can('sadaka.create')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $categoryId = (int) ($input['category_id'] ?? 0);
            $uploadMonth = (int) ($input['month'] ?? date('m'));
            $uploadYear = (int) ($input['year'] ?? date('Y'));

            // Validate month and year ranges
            if ($uploadMonth < 1 || $uploadMonth > 12) {
                Response::json(['success' => false, 'message' => 'Invalid month. Must be between 1 and 12'], 422);
                return;
            }
            if ($uploadYear < 2000 || $uploadYear > 2100) {
                Response::json(['success' => false, 'message' => 'Invalid year. Must be between 2000 and 2100'], 422);
                return;
            }

            if ($categoryId <= 0) {
                Response::json(['success' => false, 'message' => 'Category is required'], 422);
                return;
            }

            if (!isset($files['file']) || $files['file']['error'] !== UPLOAD_ERR_OK) {
                Response::json(['success' => false, 'message' => 'No file uploaded or upload error'], 422);
                return;
            }

            $file = $files['file'];
            $fileName = basename($file['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Validate file extension - only CSV is supported
            if ($fileExt !== 'csv') {
                Response::json(['success' => false, 'message' => 'Only CSV files are supported. Excel files (.xlsx, .xls) are not currently supported.'], 422);
                return;
            }

            // Validate file size (max 5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                Response::json(['success' => false, 'message' => 'File too large. Maximum size is 5MB'], 422);
                return;
            }

            $entries = [];
            try {
                $entries = $this->parseCSV($file['tmp_name']);
            } catch (Exception $e) {
                Response::json(['success' => false, 'message' => 'Error parsing CSV: ' . $e->getMessage()], 422);
                return;
            }

            // Validate row count (max 10,000)
            $maxRows = 10000;
            if (count($entries) > $maxRows) {
                Response::json(['success' => false, 'message' => "CSV exceeds maximum of {$maxRows} rows"], 422);
                @unlink($file['tmp_name']);
                return;
            }

            $successful = 0;
            $failed = 0;
            $errors = [];
            $userId = Auth::user()['id'] ?? null;

            $stmt = $this->pdo->prepare(
                'INSERT INTO sadaka_entries 
                (member_id, category_id, entry_month, entry_year, entry_week, amount, entry_date, notes, entered_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $this->pdo->beginTransaction();

            foreach ($entries as $index => $entry) {
                try {
                    // Better member matching logic
                    $memberCode = $entry['member_code'] ?? '';
                    $firstName = $entry['first_name'] ?? '';
                    $lastName = $entry['last_name'] ?? '';

                    if (empty($memberCode) && (empty($firstName) || empty($lastName))) {
                        $failed++;
                        $errors[] = "Row " . ($index + 2) . ": Either member_code or (first_name and last_name) is required";
                        continue;
                    }

                    // Find member by code if provided, otherwise by name
                    $memberStmt = null;
                    if (!empty($memberCode)) {
                        $memberStmt = $this->pdo->prepare(
                            'SELECT id FROM members WHERE member_code = ? AND member_status = "active" LIMIT 1'
                        );
                        $memberStmt->execute([$memberCode]);
                    } else {
                        $memberStmt = $this->pdo->prepare(
                            'SELECT id FROM members WHERE first_name = ? AND last_name = ? AND member_status = "active" LIMIT 1'
                        );
                        $memberStmt->execute([$firstName, $lastName]);
                    }
                    
                    $member = $memberStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$member) {
                        $failed++;
                        $errors[] = "Row " . ($index + 2) . ": Member not found";
                        continue;
                    }

                    $amount = (float) ($entry['amount'] ?? 0);
                    if ($amount <= 0 || $amount > 9999999.99) {
                        $failed++;
                        $errors[] = "Row " . ($index + 2) . ": Invalid amount (must be between 0.01 and 9,999,999.99)";
                        continue;
                    }

                    $entryDate = $entry['date'] ?? date('Y-m-d');
                    if (!$this->validateDate($entryDate)) {
                        $entryDate = date('Y-m-d');
                    }

                    $week = isset($entry['week']) ? (int) $entry['week'] : null;
                    // Validate week if provided
                    if ($week !== null && ($week < 1 || $week > 4)) {
                        $failed++;
                        $errors[] = "Row " . ($index + 2) . ": Week must be between 1 and 4";
                        continue;
                    }

                    // Sanitize notes field
                    $notes = isset($entry['notes']) ? strip_tags(trim($entry['notes'])) : '';

                    $stmt->execute([
                        $member['id'],
                        $categoryId,
                        $uploadMonth,
                        $uploadYear,
                        $week,
                        $amount,
                        $entryDate,
                        $notes,
                        $userId
                    ]);

                    $successful++;
                    $entryId = (int) $this->pdo->lastInsertId();

                    // Log individual entry for audit trail
                    Audit::log(
                        $this->pdo,
                        $userId,
                        'sadaka',
                        'entry_created_from_upload',
                        'sadaka_entries',
                        $entryId,
                        null,
                        ['member_id' => $member['id'], 'category_id' => $categoryId, 'amount' => $amount, 'from_upload' => true],
                        'Created sadaka entry from CSV upload'
                    );
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $this->pdo->commit();

            // Log upload summary
            $uploadStmt = $this->pdo->prepare(
                'INSERT INTO sadaka_uploads 
                (category_id, upload_filename, total_rows, successful_rows, failed_rows, upload_date, uploaded_by, error_log)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $uploadStmt->execute([
                $categoryId,
                $fileName,
                count($entries),
                $successful,
                $failed,
                date('Y-m-d'),
                $userId,
                $failed > 0 ? json_encode($errors) : null
            ]);
            $uploadId = (int) $this->pdo->lastInsertId();

            Audit::log(
                $this->pdo,
                $userId,
                'sadaka',
                'entries_uploaded',
                'sadaka_uploads',
                $uploadId,
                null,
                ['successful' => $successful, 'failed' => $failed, 'total' => count($entries)],
                'Uploaded sadaka entries from file'
            );

            Response::json([
                'success' => true,
                'message' => "Upload complete: {$successful} successful, {$failed} failed",
                'data' => [
                    'successful' => $successful,
                    'failed' => $failed,
                    'errors' => $errors
                ]
            ]);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            Response::json(['success' => false, 'message' => 'Error processing upload: ' . $e->getMessage()], 500);
        } finally {
            // Clean up temporary file
            if (isset($file) && isset($file['tmp_name'])) {
                @unlink($file['tmp_name']);
            }
        }
    }

    /**
     * Delete a sadaka entry
     */
    public function deleteEntry(int $entryId): void
    {
        if (!Auth::can('sadaka.delete')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $stmt = $this->pdo->prepare('DELETE FROM sadaka_entries WHERE id = ?');
            $stmt->execute([$entryId]);

            if ($stmt->rowCount() === 0) {
                Response::json(['success' => false, 'message' => 'Entry not found'], 404);
                return;
            }

            Audit::log(
                $this->pdo,
                Auth::user()['id'] ?? null,
                'sadaka',
                'entry_deleted',
                'sadaka_entries',
                $entryId,
                null,
                null,
                'Deleted sadaka entry'
            );

            Response::json(['success' => true, 'message' => 'Entry deleted successfully']);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error deleting entry: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate report for a specific month and year
     */
    public function getReport(int $year, int $month): void
    {
        if (!Auth::can('finance.view')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }

        // Validate month and year
        if ($month < 1 || $month > 12) {
            Response::json(['success' => false, 'message' => 'Invalid month'], 400);
            return;
        }
        if ($year < 2000 || $year > 2100) {
            Response::json(['success' => false, 'message' => 'Invalid year'], 400);
            return;
        }

        try {
            // Get all members
            $stmt = $this->pdo->prepare('SELECT id, member_code, first_name, last_name FROM members ORDER BY last_name, first_name');
            $stmt->execute();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $processedMembers = [];
            $totalAmount = 0;

            foreach ($members as $member) {
                $weekData = [];
                $monthTotal = 0;
                
                // Get data for each week
                for ($w = 1; $w <= 4; $w++) {
                    $weekStmt = $this->pdo->prepare('
                        SELECT COALESCE(SUM(amount), 0) as week_total 
                        FROM sadaka_entries 
                        WHERE member_id = ? 
                        AND entry_week = ? 
                        AND MONTH(entry_date) = ? 
                        AND YEAR(entry_date) = ?
                    ');
                    $weekStmt->execute([$member['id'], $w, $month, $year]);
                    $result = $weekStmt->fetch(PDO::FETCH_ASSOC);
                    $weekTotal = (float)($result['week_total'] ?? 0);
                    $weekData[] = [
                        'entry_week' => $w,
                        'week_total' => $weekTotal
                    ];
                    $monthTotal += $weekTotal;
                }

                // Get yearly total
                $yearlyStmt = $this->pdo->prepare('
                    SELECT COALESCE(SUM(amount), 0) as yearly_total 
                    FROM sadaka_entries 
                    WHERE member_id = ? 
                    AND YEAR(entry_date) = ?
                ');
                $yearlyStmt->execute([$member['id'], $year]);
                $yearlyResult = $yearlyStmt->fetch(PDO::FETCH_ASSOC);
                $yearlyTotal = (float)($yearlyResult['yearly_total'] ?? 0);

                // Only include members with entries this month
                if ($monthTotal > 0) {
                    $processedMembers[] = [
                        'id' => $member['id'],
                        'member_code' => $member['member_code'],
                        'first_name' => $member['first_name'],
                        'last_name' => $member['last_name'],
                        'week_data' => $weekData,
                        'month_total' => $monthTotal,
                        'yearly_total' => $yearlyTotal
                    ];
                    $totalAmount += $monthTotal;
                }
            }

            Response::json([
                'success' => true,
                'data' => [
                    'members' => $processedMembers,
                    'totalMembers' => count($processedMembers),
                    'totalEntries' => count($processedMembers),
                    'totalAmount' => $totalAmount
                ]
            ]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error generating report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Parse CSV file with validation and sanitization
     */
    private function parseCSV(string $filePath, int $maxSize = 5242880, int $maxRows = 10000): array
    {
        $entries = [];
        
        if (!file_exists($filePath)) {
            throw new Exception('CSV file not found');
        }
        
        if (filesize($filePath) > $maxSize) {
            throw new Exception('File exceeds maximum size limit');
        }

        if (($handle = fopen($filePath, 'r')) === false) {
            throw new Exception('Cannot open CSV file');
        }

        $headers = null;
        $rowCount = 0;
        $maxHeaderColumns = 10;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if ($rowCount++ > $maxRows) {
                    throw new Exception("CSV exceeds maximum of {$maxRows} rows");
                }

                if ($headers === null) {
                    // Validate header row
                    if (count($row) > $maxHeaderColumns) {
                        throw new Exception('CSV has too many columns');
                    }
                    $headers = array_map(function($h) {
                        return strtolower(trim($h));
                    }, $row);
                    continue;
                }

                // Validate required headers exist
                $requiredHeaders = ['member_code', 'first_name', 'last_name', 'amount'];
                $hasEitherCodeOrName = isset($headers[array_search('member_code', $headers)]) ||
                    (isset($headers[array_search('first_name', $headers)]) && isset($headers[array_search('last_name', $headers)]));
                
                if (!$hasEitherCodeOrName || !isset($headers[array_search('amount', $headers)])) {
                    throw new Exception('CSV missing required columns: (member_code OR first_name+last_name) and amount');
                }

                $entry = [];
                foreach ($headers as $index => $header) {
                    $value = $row[$index] ?? '';
                    // Sanitize: strip tags and trim
                    $entry[$header] = is_string($value) ? strip_tags(trim($value)) : trim((string) $value);
                }
                $entries[] = $entry;
            }
        } finally {
            fclose($handle);
        }

        if (empty($entries)) {
            throw new Exception('CSV file is empty (no data rows)');
        }

        return $entries;
    }

    /**
     * Validate date format
     */
    private function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Get sadaka statistics/summary
     */
    public function getStatistics(?string $year = null): void
    {
        if (!Auth::can('finance.view')) {
            Response::json(['success' => false, 'message' => 'Insufficient permissions'], 403);
            return;
        }
        try {
            $currentYear = (int) ($year ?? date('Y'));

            $stats = [];

            $stmt = $this->pdo->prepare(
                'SELECT sc.id, sc.category_name, sc.category_slug,
                        COUNT(DISTINCT se.member_id) as total_contributors,
                        COUNT(se.id) as total_entries,
                        SUM(se.amount) as total_amount
                 FROM sadaka_categories sc
                 LEFT JOIN sadaka_entries se ON sc.id = se.category_id AND se.entry_year = ?
                 WHERE sc.is_active = 1
                 GROUP BY sc.id
                 ORDER BY sc.category_name'
            );

            $stmt->execute([$currentYear]);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Response::json(['success' => true, 'data' => ['year' => $currentYear, 'categories' => $stats]]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error fetching statistics: ' . $e->getMessage()], 500);
        }
    }
}
