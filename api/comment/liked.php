<?php

require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/loggers.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$commentId = $data['comment_id'] ?? $_POST['comment_id'] ?? null;

if (!$commentId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID Comment manquant',
        'raw' => $raw
    ]);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$commentId = (int) $commentId;

$stmt = $pdo->prepare("
    SELECT 1
    FROM likes
    WHERE id_users = ?
    AND id_commentaires = ?
");

$stmt->execute([$userId, $commentId]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("
        DELETE FROM likes
        WHERE id_users = ?
        AND id_commentaires = ?
    ");
    $stmt->execute([$userId, $commentId]);

    echo json_encode([
        'success' => true,
        'favorite' => false
    ]);

    addLog(
        $pdo,
        $_SESSION['user']['id'],
        'COMMENT',
        'Retire son like sur: ' . $commentId
    );
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO likes (id_users, id_commentaires)
    VALUES (?, ?)
");

$stmt->execute([$userId, $commentId]);

echo json_encode([
    'success' => true,
    'favorite' => true
]);

addLog(
    $pdo,
    $_SESSION['user']['id'],
    'COMMENT',
    'Ajoute un like sur: ' . $commentId
);