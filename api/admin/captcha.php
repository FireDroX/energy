<?php
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) || 
    $_SESSION['user']['role'] != 1 || 
    !$_SESSION['user']['is_active']
  ) {
  http_response_code(403);
  echo json_encode(['error' => 'no_access']);
  exit;
}

$id = isset($_POST['id_captcha']) ? (int) $_POST['id_captcha'] : 0;
$question = trim($_POST['question'] ?? '');
$reponse = trim($_POST['reponse'] ?? '');

$json = json_decode($reponse, true);

function checkInputs($q, $r, $j) {
  if ($q === '' || $r === '') {
    echo json_encode(['warning' => 'missing_fields']);
    exit;
  }

  if (json_last_error() !== JSON_ERROR_NONE || !is_array($j)) {
    http_response_code(400);
    echo json_encode(['warning' => 'invalid_json']);
    exit;
  }
}

try {
  if ($id > 0) {
    if ($question === '' && $reponse === '') {
      $stmt = $pdo->prepare("DELETE FROM captcha WHERE id_captcha = :id");
      $stmt->execute([':id' => $id]);
      echo json_encode(['success' => true, 'message' => 'captcha_deleted']);
      exit;
    }

    checkInputs($question, $reponse, $json);

    $stmt = $pdo->prepare("
      UPDATE captcha 
      SET question = :question, reponse = :reponse 
      WHERE id_captcha = :id
    ");

    $stmt->execute([
      ':question' => $question,
      ':reponse' => json_encode($json),
      ':id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'captcha_updated']);

  } else {
    checkInputs($question, $reponse, $json);

    $stmt = $pdo->prepare("
      INSERT INTO captcha (question, reponse)
      VALUES (:question, :reponse)
    ");

    $stmt->execute([
      ':question' => $question,
      ':reponse' => json_encode($json)
    ]);

    echo json_encode(['success' => true, 'message' => 'captcha_created']);
  }

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'database_error',
    'details' => $e->getMessage()
  ]);
}