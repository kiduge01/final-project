<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Audit
{
    public static function log(PDO $pdo, ?int $actorId, string $module, string $action, string $entity, ?int $entityId, ?array $oldValues, ?array $newValues, string $summary): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (actor_user_id, module_name, action_name, entity_type, entity_id, change_summary, old_values, new_values, ip_address, user_agent)
             VALUES (:actor, :module, :action, :entity, :entity_id, :summary, :old_values, :new_values, :ip, :ua)'
        );

        $stmt->execute([
            ':actor' => $actorId,
            ':module' => $module,
            ':action' => $action,
            ':entity' => $entity,
            ':entity_id' => $entityId,
            ':summary' => $summary,
            ':old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            ':new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255),
        ]);
    }
}
