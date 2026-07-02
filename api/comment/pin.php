<?php

require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/loggers.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) || 
    ($_SESSION['user']['role'] != 1 && $_SESSION['user']['role'] != 3) || 
    !$_SESSION['user']['is_active']
  ) {
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
  SELECT c.is_pinned, m.nom FROM commentaires c
  INNER JOIN monsters m ON c.id_monsters = m.id_monsters
  WHERE id_commentaires = ?;
");

$stmt->execute([$commentId]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Commentaire introuvable',
        'raw' => $raw
    ]);
    exit;
}

if ($comment['is_pinned']) {
    $stmt = $pdo->prepare("
        UPDATE commentaires
        SET is_pinned = 0
        WHERE id_commentaires = ?;
    ");
    $stmt->execute([$commentId]);

    echo json_encode(['success' => true, 'message' => 'comment_unpinned']);

    addLog($pdo, $userId, 'COMMENT', 'Désépingle le commentaire ID: ' . $commentId . ' de la monster: ' . $comment['nom']);
} else {
    $stmt = $pdo->prepare("
        UPDATE commentaires
        SET is_pinned = 1
        WHERE id_commentaires = ?;
    ");
    $stmt->execute([$commentId]);

    echo json_encode(['success' => true, 'message' => 'comment_pinned']);

    addLog($pdo, $userId, 'COMMENT', 'Épingle le commentaire ID: ' . $commentId . ' de la monster: ' . $comment['nom']);
}