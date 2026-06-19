<?php

function addLog(PDO $pdo, ?int $userId, string $action, string $details = ''): void
{
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