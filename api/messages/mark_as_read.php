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

$senderId = isset($_POST['sender_id']) ? (int) $_POST['sender_id'] : null;

try {
  if ($senderId) {
    $stmt = $pdo->prepare(
      'UPDATE messages
        SET lu = 1
        WHERE receiver_id = :receiver_id AND sender_id = :sender_id AND lu = 0'
    );
    $stmt->execute([
      'receiver_id' => $userId,
      'sender_id' => $senderId,
    ]);
  } else {
    $stmt = $pdo->prepare(
      'UPDATE messages
        SET lu = 1
        WHERE receiver_id = :receiver_id AND lu = 0'
    );
    $stmt->execute(['receiver_id' => $userId]);
  }

  echo json_encode(['success' => true, 'lignes_modifiees' => $stmt->rowCount()]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error'   => 'database_error',
    'details' => $e->getMessage()
  ]);
}