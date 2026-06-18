<?php
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) || 
    ($_SESSION['user']['role'] != 1 && $_SESSION['user']['role'] != 3) || 
    !$_SESSION['user']['is_active']
  ) {
  http_response_code(403);
  echo json_encode(['error' => 'no_access']);
  exit;
}


$data = json_decode(file_get_contents('php://input'), true);

$monsterId = isset($data['monster_id']) ? (int)$data['monster_id'] : null;
$newTags   = isset($data['tags']) && is_array($data['tags']) 
              ? array_map('intval', $data['tags']) 
              : [];

if (!$monsterId) {
  http_response_code(400);
  echo json_encode(['error' => 'invalid_params']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT id_tags
    FROM monster_tags
    WHERE id_monsters = :id
  ");

  $stmt->execute([':id' => $monsterId]);

  $currentTags = $stmt->fetchAll(PDO::FETCH_COLUMN);
  
  $toDelete = array_diff($currentTags, $newTags);
  $toInsert = array_diff($newTags, $currentTags);

  $stmtDelete = $pdo->prepare("DELETE FROM monster_tags WHERE id_monsters = :id AND id_tags = :tag");
  $stmtInsert = $pdo->prepare("INSERT INTO monster_tags (id_monsters, id_tags) VALUES (:id, :tag)");

  foreach ($toDelete as $tagId) {
    $stmtDelete->execute([':id' => $monsterId, ':tag' => $tagId]);
  }
  foreach ($toInsert as $tagId) {
    $stmtInsert->execute([':id' => $monsterId, ':tag' => $tagId]);
  }

  echo json_encode(['success' => true, 'message' => 'tags_updated']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'database_error',
    'details' => $e->getMessage()
  ]);
}