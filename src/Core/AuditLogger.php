<?php

namespace App\Core;

use App\Config\Database;

/**
 * Records an entry to audit_logs for every significant action:
 * login/logout, create/update/delete, exports, printing, security changes.
 */
class AuditLogger
{
    public static function log(string $action, ?string $entity = null, ?int $entityId = null, ?string $details = null): void
    {
        $user = current_user();
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (user_id, user_name, action, entity, entity_id, details, ip_address)
             VALUES (:user_id, :user_name, :action, :entity, :entity_id, :details, :ip)'
        );
        $stmt->execute([
            'user_id' => $user['id'] ?? null,
            'user_name' => $user['full_name'] ?? 'System',
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'details' => $details,
            'ip' => client_ip(),
        ]);
    }

    public static function logExport(string $type, ?int $depositFormId, string $context = 'invoice'): void
    {
        $user = current_user();
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'INSERT INTO export_logs (user_id, deposit_form_id, export_type, context) VALUES (:user_id, :form_id, :type, :context)'
        );
        $stmt->execute([
            'user_id' => $user['id'] ?? null,
            'form_id' => $depositFormId,
            'type' => $type,
            'context' => $context,
        ]);
        self::log('export_' . $type, $context, $depositFormId, strtoupper($type) . ' export');
    }
}
