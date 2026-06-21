<?php

function getConversationsForUser(PDO $pdo, int $userId): array {
    $sql = "
        SELECT u.id_users, u.pseudo,
               m.contenu AS dernier_message,
               m.date_envoie AS derniere_date,
               m.sender_id
        FROM messages m
        JOIN users u
          ON u.id_users = IF(m.sender_id = :user_id, m.receiver_id, m.sender_id)
        WHERE m.id_messages IN (
            SELECT MAX(id_messages)
            FROM messages
            WHERE sender_id = :user_id OR receiver_id = :user_id
            GROUP BY IF(sender_id = :user_id, receiver_id, sender_id)
        )
        ORDER BY derniere_date DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['non_lus'] = countUnreadFrom($pdo, $userId, (int) $row['id_users']);
    }

    return $rows;
}

function countUnreadFrom(PDO $pdo, int $userId, int $otherId): int {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM messages
        WHERE receiver_id = :user_id AND sender_id = :other_id AND lu = 0
    ");
    $stmt->execute(['user_id' => $userId, 'other_id' => $otherId]);
    return (int) $stmt->fetchColumn();
}

function countTotalUnread(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM messages
        WHERE receiver_id = :user_id AND lu = 0
    ");
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function shortenText(string $text, int $maxLength = 60): string {
    if (mb_strlen($text) <= $maxLength) {
        return $text;
    }
    return mb_substr($text, 0, $maxLength) . '…';
}