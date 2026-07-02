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
$userId = $data['user_id'] ?? $_POST['user_id'] ?? null;

if (!$commentId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID Comment manquant',
        'raw' => $raw
    ]);
    exit;
}

$loggedUserId = (int) $_SESSION['user']['id'];
$commentId = (int) $commentId;

$stmt = $pdo->prepare("
    SELECT 
        c.id_commentaires, c.commentaire, u.id_users, u.pseudo, m.nom
    FROM commentaires c
    INNER JOIN users u ON c.id_users = u.id_users
    INNER JOIN monsters m ON c.id_monsters = m.id_monsters
    WHERE id_commentaires = ?;
");
$stmt->execute([$commentId]);

$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Commentaire introuvable'
    ]);
    exit;
}

$ownerId = (int) $comment['id_users'];

if (
    $ownerId !== $loggedUserId &&
    !in_array($_SESSION['user']['role'], [1, 3])
) {
    http_response_code(403);
    echo json_encode(['warning' => true]);
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM commentaires
    WHERE id_commentaires = ?
");
$stmt->execute([$commentId]);

if ($ownerId !== $loggedUserId) {
    addLog(
        $pdo,
        $loggedUserId,
        'COMMENT',
        'Supprime le commentaire (' . htmlspecialchars($comment['commentaire']) . ') de ' . htmlspecialchars($comment['pseudo']) . ' sur la monster ' . htmlspecialchars($comment['nom'])
    );
} else {
    addLog(
        $pdo,
        $loggedUserId,
        'COMMENT',
        'Supprime son commentaire (' . htmlspecialchars($comment['commentaire']) . ') sur la monster' . htmlspecialchars($comment['nom'])
    );
}

echo json_encode(['success' => true]);
exit;