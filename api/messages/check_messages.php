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

try {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS nb_non_lus, MAX(id_messages) AS dernier_id
            FROM messages
            WHERE receiver_id = :receiver_id AND lu = 0'
    );
    $stmt->execute(['receiver_id' => $userId]);
    $result = $stmt->fetch();

    echo json_encode([
        'nb_non_lus' => (int) $result['nb_non_lus'],
        'dernier_id' => $result['dernier_id'] !== null ? (int) $result['dernier_id'] : 0,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'database_error',
        'details' => $e->getMessage()
    ]);
}