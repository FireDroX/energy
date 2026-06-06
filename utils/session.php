<?php
require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (isset($_SESSION['user']) && isset($_SESSION['user']['lastUpdate']) && $_SESSION['user']['lastUpdate'] + 300 < time()) {
  require_once __DIR__ . '/functions.php';

  try {
    $sql = "SELECT id_users, pseudo, mail, id_role, deactivated
            FROM users
            WHERE mail = :mail";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'mail' => $_SESSION['user']['email']
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
      session_destroy();
      header('Location: /');
      exit;
    }

    $_SESSION['user'] = [
        'id' => $user['id_users'],
        'pseudo' => $user['pseudo'],
        'email' => $user['mail'],
        'role' => $user['id_role'],
        'is_active' => is_null($user['deactivated']),
        'lastUpdate' => time()
    ];
  } catch (PDOException $e) {
    exit;
  }
}