<?php
require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_active']) {
    http_response_code(401);
    echo json_encode(['error' => 'no_access']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$otherId = isset($_GET['with']) ? (int) $_GET['with'] : 0;
$afterId = isset($_GET['after_id']) ? (int) $_GET['after_id'] : 0;

if ($otherId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id_messages, contenu, date_envoie, sender_id, receiver_id
        FROM messages
        WHERE id_messages > :after_id
          AND (
                (sender_id = :user_id AND receiver_id = :other_id)
             OR (sender_id = :other_id AND receiver_id = :user_id)
          )
        ORDER BY id_messages ASC
    ");
    $stmt->execute([
        'after_id' => $afterId,
        'user_id' => $userId,
        'other_id' => $otherId,
    ]);
    $messages = $stmt->fetchAll();

    $markRead = $pdo->prepare("
        UPDATE messages SET lu = 1
        WHERE receiver_id = :user_id AND sender_id = :other_id AND lu = 0
    ");
    $markRead->execute(['user_id' => $userId, 'other_id' => $otherId]);

    echo json_encode(['messages' => $messages]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error']);
}