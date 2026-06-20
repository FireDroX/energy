<?php
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 
require_once __DIR__ . '/../../utils/loggers.php'; 

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) || 
    !$_SESSION['user']['is_active']
  ) {
  http_response_code(403);
  echo json_encode(['error' => 'no_access']);
  exit;
}

$value = isset($_POST['value']) ? (int) $_POST['value'] : 0;

try {
  if ($value == 0 || $value == 1) {
    $stmt = $pdo->prepare("UPDATE users SET newsletter = :value WHERE id_users = :id");
    $stmt->execute([
      ':value' => $value,
      ':id' => $_SESSION['user']['id']
    ]);

    if ($value == 1) {
      echo json_encode(['success' => true, 'message' => 'newsletter_activate']);
      addLog($pdo, $_SESSION['user']['id'], 'ACCOUNT', 'Activation de la newsletter');
    } else {
      echo json_encode(['success' => true, 'message' => 'newsletter_deactivate']);
      addLog($pdo, $_SESSION['user']['id'], 'ACCOUNT', 'Désactivation de la newsletter');
    }
  } else {
    echo json_encode(['error' => 'invalid_params']);
  }
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'database_error',
    'details' => $e->getMessage()
  ]);
}