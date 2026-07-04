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

$easterEggName = $data['easter_egg_name'] ?? $_POST['easter_egg_name'] ?? null;
$easterEggReward = $data['easter_egg_reward'] ?? $_POST['easter_egg_reward'] ?? null;

if (!$easterEggName) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'EasterEgg manquant',
        'raw' => $raw
    ]);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT 1 FROM user_eggs u
    INNER JOIN easter_eggs e ON e.id_egg = u.id_egg
    WHERE u.id_users = ?
    AND e.nom = ?
");

$stmt->execute([$userId, $easterEggName]);

if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode([
        'warning' => true,
        'message' => 'color_not_unlocked'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE users SET color_selected = ?
    WHERE id_users = ?;
");
$stmt->execute([$easterEggReward, $userId]);

echo json_encode([
    'success' => true,
    'message' => 'color_changed'
]);

addLog(
    $pdo,
    $_SESSION['user']['id'],
    'SKIN',
    'Sélectionne le skin: ' . $easterEggName
);

exit;