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

$monsterId = $data['monster_id'] ?? $_POST['monster_id'] ?? null;
$note = $data['note'] ?? $_POST['note'] ?? null;

if (!$monsterId || !is_numeric($monsterId) || $note === null || !is_numeric($note) || $note < 0 || $note > 5) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres invalides',
        'raw' => $raw
    ]);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$monsterId = (int) $monsterId;
$note = (int) $note;

$stmt = $pdo->prepare("
    SELECT 1
    FROM notes
    WHERE id_users = ?
    AND id_monsters = ?
");

$stmt->execute([$userId, $monsterId]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("
        UPDATE notes
        SET note = ?, date_note = NOW()        
        WHERE id_users = ?
        AND id_monsters = ?
    ");
    $stmt->execute([$note, $userId, $monsterId]);

    echo json_encode([
        'success' => true,
        'message' => 'note_updated'
    ]);

    addLog(
        $pdo,
        $_SESSION['user']['id'],
        'NOTES',
        'Met à jour sa note sur: ' . $monsterId
    );
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO notes (id_users, id_monsters, note, date_note)
    VALUES (?, ?, ?, NOW())
");

$stmt->execute([$userId, $monsterId, $note]);

echo json_encode([
    'success' => true,
    'message' => 'note_created'
]);

addLog(
    $pdo,
    $_SESSION['user']['id'],
    'NOTES',
    'Ajoute une note sur: ' . $monsterId
);