<?php
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 
require_once __DIR__ . '/../../utils/loggers.php'; 

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

$id = isset($_POST['id_monsters']) ? (int) $_POST['id_monsters'] : 0;
$nom = trim(strtolower(str_replace(' ', '_', $_POST['nom'])) ?? '');
$image = trim($_POST['image'] ?? '');

function checkInputs($q, $r) {
  if ($q === '' || $r === '') {
    echo json_encode(['warning' => 'missing_fields']);
    exit;
  }
}

try {
  if ($id > 0) {
    checkInputs($nom, $image);

    $stmt = $pdo->prepare("
      UPDATE monsters 
      SET nom = :nom, image = :image 
      WHERE id_monsters = :id
    ");

    $stmt->execute([
      ':nom' => $nom,
      ':image' => $image,
      ':id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'monster_updated']);
    addLog($pdo, $_SESSION['user']['id'], 'MONSTER', 'Update de  : ' . $nom);

  } else {
    checkInputs($nom, $image);

    $stmt = $pdo->prepare("
      INSERT INTO monsters (nom, image)
      VALUES (:nom, :image)
    ");

    $stmt->execute([
      ':nom' => $nom,
      ':image' => $image
    ]);

    echo json_encode(['success' => true, 'message' => 'monster_created']);
    addLog($pdo, $_SESSION['user']['id'], 'MONSTER', 'Création de  : ' . $nom);
  }

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'database_error',
    'details' => $e->getMessage()
  ]);
}