<?php

require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$monsterId = $data['monster_id'] ?? $_POST['monster_id'] ?? null;

if (!$monsterId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID Monster manquant',
        'raw' => $raw
    ]);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$monsterId = (int) $monsterId;

$stmt = $pdo->prepare("
    SELECT 1
    FROM monster_favorites
    WHERE id_users = ?
    AND id_monsters = ?
");

$stmt->execute([$userId, $monsterId]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("
        DELETE FROM monster_favorites
        WHERE id_users = ?
        AND id_monsters = ?
    ");
    $stmt->execute([$userId, $monsterId]);

    echo json_encode([
        'success' => true,
        'favorite' => false
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO monster_favorites (id_users, id_monsters)
    VALUES (?, ?)
");

$stmt->execute([$userId, $monsterId]);

echo json_encode([
    'success' => true,
    'favorite' => true
]);