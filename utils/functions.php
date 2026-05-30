<?php 
  
  function loadEnv($path) {
    $lines = file($path);

    foreach ($lines as $line) {
      $line = trim($line);

      if ($line === '' || $line[0] === '#') continue;
      
      $parts = explode('=', $line);
      
      $key = $parts[0];
      $value = $parts[1];
      
      $_ENV[$key] = $value;
      }
    }
  loadEnv(__DIR__ . '/../.env');
    
  function getMonster(PDO $pdo, $nom) {
    try {
      $stmt = $pdo->prepare("SELECT
          m.image,
          GROUP_CONCAT(DISTINCT t.nom) AS tags,
          ROUND(AVG(n.note), 2) AS score
        FROM monsters m
        LEFT JOIN monster_tags mt ON m.id_monsters = mt.id_monsters
        LEFT JOIN tags t ON mt.id_tags = t.id_tags
        LEFT JOIN notes n ON m.id_monsters = n.id_monsters
        WHERE m.nom = :nom
        GROUP BY m.id_monsters;"
      );
      $stmt->execute(['nom' => $nom]);

      $monster = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($monster === false) goHome();

    } catch (PDOException $e) {
      $monster = null;
    }
    return $monster;
  }

  function getComments(PDO $pdo, $nom) {
    try {
      $stmt = $pdo->prepare("SELECT c.id_commentaires, 
          c.id_parent, 
          c.commentaire, 
          c.is_pinned,
          u.pseudo
        FROM monsters AS m
        INNER JOIN monster_tags AS mt ON mt.id_monsters = m.id_monsters
        INNER JOIN tags ON tags.id_tags = mt.id_tags
        INNER JOIN commentaires AS c ON c.id_monsters = m.id_monsters
        INNER JOIN users AS u ON u.id_users = c.id_users
        WHERE m.nom = :nom;"
      );
      $stmt->execute(['nom' => $nom]);

      $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $comments = null;
    }
    return $comments;
  }

  function generateCaptcha(PDO $pdo) {
    try {
      $stmt = $pdo->query("SELECT * FROM captcha ORDER BY RAND() LIMIT 1");

      $captcha = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $captcha = ['id_captcha' => 0, 'question' => 'Quels sont les 2 VRAI délégués de la classe ?', 'reponse' => '["hassrol","adrien"]'];
    }

    $_SESSION['captcha_answer'] = json_decode($captcha['reponse'], true);
    return $captcha;
  }
?>