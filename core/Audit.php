<?php

class Audit
{
    public static function record(string $action, string $entity, ?int $entityId = null, string $details = ''): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'INSERT INTO activity_log (user_id, user_name, action, entity, entity_id, details)
                 VALUES (:user_id, :user_name, :action, :entity, :entity_id, :details)'
            );
            $stmt->execute([
                'user_id' => $_SESSION['user_id'] ?? null,
                'user_name' => $_SESSION['user_name'] ?? 'Sistema',
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'details' => $details,
            ]);
        } catch (Throwable $e) {
            // No interrumpir el flujo principal si la bitacora falla.
        }
    }
}
