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

$userId = $_SESSION['user']['id'];
$parentId = $data['parent_id'] ?? $_POST['parent_id'] ?? null;
$monsterName = $data['monster_name'] ?? "";
$content = $data['content'] ?? "";

$stmt = $pdo->prepare("SELECT id_monsters FROM monsters WHERE nom = ?");
$stmt->execute([$monsterName]);
$monsterId = $stmt->fetchColumn();

if (!$monsterId) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Monster not found']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO commentaires (
        id_parent,
        commentaire,
        id_monsters,
        id_users
    ) VALUES (?, ?, ?, ?)
");

$stmt->execute([
    $parentId,
    $content,
    $monsterId,
    $userId
]);

echo json_encode(['success' => true]);

addLog(
  $pdo,
  $_SESSION['user']['id'],
  'COMMENT',
  'Ajoute un like commentaire: ' . $monsterName
);