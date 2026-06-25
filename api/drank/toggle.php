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
    SELECT COUNT(*) as amount_drank_today FROM monster_drinks md
    INNER JOIN users u ON u.id_users = md.id_users
    INNER JOIN monsters m ON m.id_monsters = md.id_monsters
    WHERE u.id_users = :id AND m.id_monsters = :monster AND md.date_drink >= NOW() - INTERVAL 1 DAY
");

$stmt->execute([
    ':id' => $userId,
    ':monster' => $monsterId
]);

$row = $stmt->fetch();
$alreadyDrank = $row && (int) $row['amount_drank_today'] > 0;

if ($alreadyDrank) {
    $stmt = $pdo->prepare("
        DELETE FROM monster_drinks
        WHERE id_users = ? AND id_monsters = ? AND date_drink >= NOW() - INTERVAL 1 DAY
    ");
    $stmt->execute([$userId, $monsterId]);

    echo json_encode([
        'success' => true,
        'drank' => false
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO monster_drinks (id_users, id_monsters)
    VALUES (?, ?)
");
$stmt->execute([$userId, $monsterId]);

echo json_encode([
    'success' => true,
    'drank' => true
]);