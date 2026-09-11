<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use PDO;

/**
 * Zone Management Controller
 * Handles zones, zone members, ushers, events, and offerings
 */
final class ZoneController
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Get all zones with stats
     */
    public function list(): void
    {
        try {
            $stmt = $this->pdo->query('
                SELECT 
                    z.id, z.name, z.location, z.description, z.zone_leader_id, z.is_active,
                    z.created_at, z.updated_at,
                    (SELECT COUNT(*) FROM zone_members WHERE zone_id = z.id AND is_active = 1) as member_count,
                    (SELECT COUNT(*) FROM zone_ushers WHERE zone_id = z.id AND is_active = 1) as usher_count,
                    (SELECT COUNT(*) FROM zone_events WHERE zone_id = z.id) as event_count
                FROM zones z
                WHERE z.is_active = 1
                ORDER BY z.name ASC
            ');
            $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::json(['success' => true, 'data' => $zones]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error fetching zones: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get single zone
     */
    public function get(int $id): void
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    z.id, z.name, z.location, z.description, z.zone_leader_id, z.is_active,
                    z.created_at, z.updated_at,
                    (SELECT COUNT(*) FROM zone_members WHERE zone_id = z.id AND is_active = 1) as member_count,
                    (SELECT COUNT(*) FROM zone_ushers WHERE zone_id = z.id AND is_active = 1) as usher_count,
                    (SELECT COUNT(*) FROM zone_events WHERE zone_id = z.id) as event_count
                FROM zones z
                WHERE z.id = ?
            ');
            $stmt->execute([$id]);
            $zone = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$zone) {
                http_response_code(404);
                Response::json(['success' => false, 'message' => 'Zone not found'], 404);
                return;
            }
            
            Response::json(['success' => true, 'data' => $zone]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create zone
     */
    public function create(array $input): void
    {
        $name = trim((string) ($input['name'] ?? ''));
        $location = trim((string) ($input['location'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $zone_leader_id = $input['zone_leader_id'] ? (int) $input['zone_leader_id'] : null;

        // Validate
        if (!$name || !$location) {
            Response::json(['success' => false, 'message' => 'Zone name and location are required'], 422);
            return;
        }

        try {
            // Check if zone already exists
            $stmt = $this->pdo->prepare('SELECT id FROM zones WHERE name = ?');
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                Response::json(['success' => false, 'message' => 'Zone already exists'], 400);
                return;
            }

            // Insert zone
            $stmt = $this->pdo->prepare('
                INSERT INTO zones (name, location, description, zone_leader_id, is_active, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())
            ');
            $stmt->execute([$name, $location, $description, $zone_leader_id]);
            $zoneId = (int) $this->pdo->lastInsertId();

            Response::json(['success' => true, 'message' => 'Zone created', 'data' => ['id' => $zoneId]]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error creating zone: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update zone
     */
    public function update(int $id, array $input): void
    {
        $name = trim((string) ($input['name'] ?? ''));
        $location = trim((string) ($input['location'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $zone_leader_id = $input['zone_leader_id'] ? (int) $input['zone_leader_id'] : null;

        if (!$name || !$location) {
            Response::json(['success' => false, 'message' => 'Zone name and location are required'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare('
                UPDATE zones 
                SET name = ?, location = ?, description = ?, zone_leader_id = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([$name, $location, $description, $zone_leader_id, $id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                Response::json(['success' => false, 'message' => 'Zone not found'], 404);
                return;
            }

            Response::json(['success' => true, 'message' => 'Zone updated']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error updating zone: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete zone
     */
    public function delete(int $id): void
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE zones SET is_active = 0, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                Response::json(['success' => false, 'message' => 'Zone not found'], 404);
                return;
            }

            Response::json(['success' => true, 'message' => 'Zone deleted']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error deleting zone: ' . $e->getMessage()], 500);
        }
    }

    // ═════════════════════════════════════════════════════════════ ZONE MEMBERS

    /**
     * Get all zone members
     */
    public function listMembers(int $zoneId = 0): void
    {
        try {
            $query = '
                SELECT 
                    zm.id, zm.zone_id, zm.member_id, zm.assigned_date, zm.notes, zm.is_active,
                    m.first_name, m.last_name, m.phone,
                    z.name as zone_name
                FROM zone_members zm
                JOIN members m ON m.id = zm.member_id
                JOIN zones z ON z.id = zm.zone_id
                WHERE zm.is_active = 1
            ';
            
            if ($zoneId > 0) {
                $query .= ' AND zm.zone_id = ' . (int)$zoneId;
            }
            
            $query .= ' ORDER BY z.name, m.first_name, m.last_name';
            $stmt = $this->pdo->query($query);
            
            $members = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $m) {
                $m['member_name'] = $m['first_name'] . ' ' . $m['last_name'];
                $members[] = $m;
            }
            
            Response::json(['success' => true, 'data' => $members]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add member to zone
     */
    public function addMember(array $input): void
    {
        $zone_id = (int) ($input['zone_id'] ?? 0);
        $member_id = (int) ($input['member_id'] ?? 0);
        $notes = trim((string) ($input['notes'] ?? ''));

        if (!$zone_id || !$member_id) {
            Response::json(['success' => false, 'message' => 'Zone and member are required'], 422);
            return;
        }

        try {
            // Check if already exists
            $stmt = $this->pdo->prepare('SELECT id FROM zone_members WHERE zone_id = ? AND member_id = ?');
            $stmt->execute([$zone_id, $member_id]);
            if ($stmt->fetch()) {
                Response::json(['success' => false, 'message' => 'Member already in this zone'], 400);
                return;
            }

            $stmt = $this->pdo->prepare('
                INSERT INTO zone_members (zone_id, member_id, notes, is_active, assigned_date)
                VALUES (?, ?, ?, 1, NOW())
            ');
            $stmt->execute([$zone_id, $member_id, $notes]);

            Response::json(['success' => true, 'message' => 'Member added to zone']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove member from zone
     */
    public function removeMember(int $id): void
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE zone_members SET is_active = 0 WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                Response::json(['success' => false, 'message' => 'Not found'], 404);
                return;
            }

            Response::json(['success' => true, 'message' => 'Member removed']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ═════════════════════════════════════════════════════════════ ZONE USHERS

    /**
     * Get all zone ushers
     */
    public function listUshers(int $zoneId = 0): void
    {
        try {
            $query = '
                SELECT 
                    zu.id, zu.zone_id, zu.member_id, zu.usher_role, zu.assigned_date, zu.is_active,
                    m.first_name, m.last_name, m.phone,
                    z.name as zone_name
                FROM zone_ushers zu
                JOIN members m ON m.id = zu.member_id
                JOIN zones z ON z.id = zu.zone_id
                WHERE zu.is_active = 1
            ';
            
            if ($zoneId > 0) {
                $query .= ' AND zu.zone_id = ' . (int)$zoneId;
            }
            
            $query .= ' ORDER BY z.name, zu.usher_role DESC, m.first_name';
            $stmt = $this->pdo->query($query);
            
            $ushers = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
                $u['member_name'] = $u['first_name'] . ' ' . $u['last_name'];
                $ushers[] = $u;
            }
            
            Response::json(['success' => true, 'data' => $ushers]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Register usher for zone
     */
    public function addUsher(array $input): void
    {
        $zone_id = (int) ($input['zone_id'] ?? 0);
        $member_id = (int) ($input['member_id'] ?? 0);
        $usher_role = trim((string) ($input['usher_role'] ?? ''));

        if (!$zone_id || !$member_id || !in_array($usher_role, ['head', 'assistant'])) {
            Response::json(['success' => false, 'message' => 'Zone, member, and valid role are required'], 422);
            return;
        }

        try {
            // Check if already exists
            $stmt = $this->pdo->prepare('SELECT id FROM zone_ushers WHERE zone_id = ? AND member_id = ? AND usher_role = ?');
            $stmt->execute([$zone_id, $member_id, $usher_role]);
            if ($stmt->fetch()) {
                Response::json(['success' => false, 'message' => 'Usher already registered in this role'], 400);
                return;
            }

            $stmt = $this->pdo->prepare('
                INSERT INTO zone_ushers (zone_id, member_id, usher_role, is_active, assigned_date)
                VALUES (?, ?, ?, 1, NOW())
            ');
            $stmt->execute([$zone_id, $member_id, $usher_role]);

            Response::json(['success' => true, 'message' => 'Usher registered']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove usher
     */
    public function removeUsher(int $id): void
    {
        try {
            $stmt = $this->pdo->prepare('UPDATE zone_ushers SET is_active = 0 WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                Response::json(['success' => false, 'message' => 'Not found'], 404);
                return;
            }

            Response::json(['success' => true, 'message' => 'Usher removed']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ═════════════════════════════════════════════════════════════ ZONE EVENTS

    /**
     * Get zone events
     */
    public function listEvents(int $zoneId = 0): void
    {
        try {
            $query = '
                SELECT 
                    ze.id, ze.zone_id, ze.title, ze.description, ze.event_date, 
                    ze.venue, ze.status, ze.expected_attendance, ze.created_at,
                    z.name as zone_name,
                    (SELECT SUM(amount) FROM zone_event_offerings WHERE zone_event_id = ze.id) as total_offerings
                FROM zone_events ze
                JOIN zones z ON z.id = ze.zone_id
                WHERE 1=1
            ';
            
            if ($zoneId > 0) {
                $query .= ' AND ze.zone_id = ' . (int)$zoneId;
            }
            
            $query .= ' ORDER BY ze.event_date DESC';
            $stmt = $this->pdo->query($query);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            Response::json(['success' => true, 'data' => $events]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create zone event
     */
    public function createEvent(array $input): void
    {
        $zone_id = (int) ($input['zone_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $event_date = trim((string) ($input['event_date'] ?? ''));
        $venue = trim((string) ($input['venue'] ?? ''));
        $expected_attendance = (int) ($input['expected_attendance'] ?? 0);

        if (!$zone_id || !$title || !$event_date) {
            Response::json(['success' => false, 'message' => 'Zone, title, and date are required'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO zone_events (zone_id, title, description, event_date, venue, expected_attendance, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, "planned", NOW())
            ');
            $stmt->execute([$zone_id, $title, $description, $event_date, $venue, $expected_attendance]);
            $eventId = (int) $this->pdo->lastInsertId();

            Response::json(['success' => true, 'message' => 'Event created', 'data' => ['id' => $eventId]]);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Record offering for zone event
     */
    public function recordOffering(array $input): void
    {
        $zone_event_id = (int) ($input['zone_event_id'] ?? 0);
        $member_id = (int) ($input['member_id'] ?? 0);
        $offering_type = trim((string) ($input['offering_type'] ?? 'offering'));
        $amount = (float) ($input['amount'] ?? 0);
        $payment_method = trim((string) ($input['payment_method'] ?? 'cash'));
        $reference_no = trim((string) ($input['reference_no'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));

        if (!$zone_event_id || !$amount || $amount <= 0) {
            Response::json(['success' => false, 'message' => 'Event and valid amount are required'], 422);
            return;
        }

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO zone_event_offerings 
                (zone_event_id, member_id, offering_type, amount, payment_method, reference_no, notes, recorded_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$zone_event_id, $member_id ?: null, $offering_type, $amount, $payment_method, $reference_no, $notes, 1]);

            Response::json(['success' => true, 'message' => 'Offering recorded']);
        } catch (\Throwable $e) {
            http_response_code(500);
            Response::json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
