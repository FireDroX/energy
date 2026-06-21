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
$receiverId = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
$contenu = trim($_POST['contenu'] ?? '');

if ($receiverId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'destinataire_invalide']);
    exit;
}

if ($contenu === '') {
    http_response_code(400);
    echo json_encode(['error' => 'empty_message']);
    exit;
}

if (mb_strlen($contenu) > 255) {
    http_response_code(400);
    echo json_encode(['error' => '255_or_more']);
    exit;
}

if ($receiverId === $userId) {
    http_response_code(400);
    echo json_encode(['error' => 'not_yourself']);
    exit;
}

try {
    // Vérifie que le destinataire existe bien
    $check = $pdo->prepare('SELECT id_users FROM users WHERE id_users = :id');
    $check->execute(['id' => $receiverId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'destinataire_invalide']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO messages (contenu, date_envoie, sender_id, receiver_id, lu)
         VALUES (:contenu, NOW(), :sender_id, :receiver_id, 0)'
    );
    $stmt->execute([
        'contenu' => $contenu,
        'sender_id' => $userId,
        'receiver_id' => $receiverId,
    ]);

    $newId = $pdo->lastInsertId();

    // On relit le message inséré pour renvoyer une donnée cohérente au client
    $get = $pdo->prepare('SELECT id_messages, contenu, date_envoie, sender_id, receiver_id FROM messages WHERE id_messages = :id');
    $get->execute(['id' => $newId]);
    $message = $get->fetch();

    echo json_encode(['success' => true, 'message' => $message]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'database_error',
        'details' => $e->getMessage()
    ]);
}