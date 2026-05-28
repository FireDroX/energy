<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
  http_response_code(403);
  echo json_encode(['error' => 'Forbidden']);
  exit;
}

require_once __DIR__ . '/../../utils/functions.php';

if (!isset($_POST['csrf']) || !isset($_SESSION['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
  http_response_code(403);
  echo json_encode(['error' => 'Invalid CSRF token']);
  exit;
}

$id = isset($_POST['id_captcha']) ? (int) $_POST['id_captcha'] : 0;
$question = trim($_POST['question'] ?? '');
$reponse = trim($_POST['reponse'] ?? '');

if ($question === '' || $reponse === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Question et réponse obligatoires']);
  exit;
}

$json = json_decode($reponse, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($json)) {
  http_response_code(400);
  echo json_encode(['error' => 'La réponse doit être un JSON valide (array)']);
  exit;
}

try {
  $pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']};charset=utf8",
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD']
  );

  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if ($id > 0) {
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

    echo json_encode(['success' => true, 'message' => 'Captcha mis à jour']);

  } else {
    $stmt = $pdo->prepare("
      INSERT INTO captcha (question, reponse)
      VALUES (:question, :reponse)
    ");

    $stmt->execute([
      ':question' => $question,
      ':reponse' => json_encode($json)
    ]);

    echo json_encode(['success' => true, 'message' => 'Captcha créé']);
  }

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Database error',
    'details' => $e->getMessage()
  ]);
}