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
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$id = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
$pseudo = trim($_POST['pseudo'] ?? '');
$mail = trim($_POST['mail'] ?? '');
$mdp = trim($_POST['mdp'] ?? '');
$role = isset($_POST['id_role']) ? (int) $_POST['id_role'] : 0;
$active = isset($_POST['active']) ? (int) $_POST['active'] : 1;

function checkInputs($p, $m, $r, $pdo) {
  if ($p === '' || $m === '') {
    echo json_encode(['error' => 'Pseudo et Mail sont requis !']);
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM roles WHERE id_role = :id");
  $stmt->execute([':id' => $r]);

  if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(400);
    echo json_encode(['error' => 'Role non existant !']);
    exit;
  }
}

try {
  if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_users = :id");
    $stmt->execute([':id' => $id]);
    $prevUser = $stmt->fetch(PDO::FETCH_ASSOC);

    checkInputs($pseudo, $mail, $role, $pdo);

    if (is_null($prevUser['deactivated']) && $active === 0) {
      $stmt = $pdo->prepare("
        UPDATE users
        SET deactivated = NOW(),
          pseudo = :pseudo,
          mail = :mail,
          mdp = :mdp,
          id_role = :role
        WHERE id_users = :id
      ");

      $stmt->execute([
        ':pseudo' => $pseudo,
        ':mail' => $mail,
        ':mdp' => $mdp,
        ':role' => $role,
        ':id' => $id
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'Utilisateur désactivé / modifié'
      ]);
      exit;
    } else if (!is_null($prevUser['deactivated']) && $active === 1) {
      $stmt = $pdo->prepare("
        UPDATE users
        SET deactivated = NULL,
          pseudo = :pseudo,
          mail = :mail,
          mdp = :mdp,
          id_role = :role
        WHERE id_users = :id
      ");

      $stmt->execute([
        ':pseudo' => $pseudo,
        ':mail' => $mail,
        ':mdp' => $mdp,
        ':role' => $role,
        ':id' => $id
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'Utilisateur réactivé / modifié'
      ]);
      exit;
    }

    $stmt = $pdo->prepare("
      UPDATE users
      SET pseudo = :pseudo,
        mail = :mail,
        mdp = :mdp,
        id_role = :role
      WHERE id_users = :id
    ");

    $stmt->execute([
      ':pseudo' => $pseudo,
      ':mail' => $mail,
      ':mdp' => $mdp,
      ':role' => $role,
      ':id' => $id
    ]);

    echo json_encode([
      'success' => true,
      'message' => 'Utilisateur mis à jour'
    ]);
    exit;
  }

  checkInputs($pseudo, $mail, $role, $pdo);

  $stmt = $pdo->prepare("SELECT mail FROM users WHERE mail = :mail");
  $stmt->execute([':mail' => $mail]);

  if ($stmt->fetch()) {
    echo json_encode(['error' => 'Mail existant !']);
    exit;
  }

  $stmt = $pdo->prepare("
    INSERT INTO users (pseudo, mail, mdp, id_role)
    VALUES (:pseudo, :mail, :mdp, :role)
  ");

  $stmt->execute([
    ':pseudo' => $pseudo,
    ':mail' => $mail,
    ':mdp' => password_hash($mdp, PASSWORD_DEFAULT),
    ':role' => $role,
  ]);

  echo json_encode(['success' => true, 'message' => 'Utilisateur créé']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Database error',
    'details' => $e->getMessage()
  ]);
}