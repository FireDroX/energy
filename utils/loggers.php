<?php

function addLog(PDO $pdo, ?int $userId, string $action, string $details = ''): void {
    $stmt = $pdo->prepare("
        SELECT 1
        FROM logs
        WHERE id_users = :id_users
            AND action = :action
            AND created_at >= (NOW() - INTERVAL 15 SECOND)
        LIMIT 1
    ");

    $stmt->execute([
        ':id_users' => $userId,
        ':action' => $action
    ]);

    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists > 0) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO logs (
            id_users,
            action,
            details
        )
        VALUES (
            :id_users,
            :action,
            :details
        )
    ");

    $stmt->execute([
        ':id_users' => $userId,
        ':action' => $action,
        ':details' => $details
    ]);
}
?>