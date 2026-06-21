<?php
require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/conversations.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_active']) {
    http_response_code(401);
    echo json_encode(['error' => 'no_access']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

try {
    $conversations = getConversationsForUser($pdo, $userId);

    $result = [];
    foreach ($conversations as $conv) {
        $result[] = [
            'id_users' => (int) $conv['id_users'],
            'pseudo' => $conv['pseudo'],
            'preview' => shortenText($conv['dernier_message']),
            'is_from_them' => (int) $conv['sender_id'] !== $userId,
            'derniere_date' => $conv['derniere_date'],
            'non_lus' => $conv['non_lus'],
        ];
    }

    $totalNonLus = 0;
    foreach ($result as $conv) {
        $totalNonLus += $conv['non_lus'];
    }

    echo json_encode([
        'conversations' => $result,
        'total_non_lus' => $totalNonLus,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error']);
}