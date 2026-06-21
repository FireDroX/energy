<?php
require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/messages/conversations.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_active']) {
    http_response_code(401);
    echo json_encode(['error' => 'no_access']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

try {
    $nbNonLus = countTotalUnread($pdo, $userId);
    echo json_encode(['nb_non_lus' => $nbNonLus]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error']);
}