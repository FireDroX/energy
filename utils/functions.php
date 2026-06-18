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
          ROUND(AVG(n.note), 2) AS score,
          COUNT(n.note) AS nb_notes
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
      $stmt = $pdo->prepare("SELECT 
          c.id_commentaires,
          c.id_parent,
          c.commentaire,
          c.date,
          c.is_pinned,
          u.pseudo,
          COUNT(l.id_commentaires) AS nb_likes
      FROM monsters AS m
      INNER JOIN monster_tags AS mt ON mt.id_monsters = m.id_monsters
      INNER JOIN tags ON tags.id_tags = mt.id_tags
      INNER JOIN commentaires AS c ON c.id_monsters = m.id_monsters
      INNER JOIN users AS u ON u.id_users = c.id_users
      LEFT JOIN likes AS l ON l.id_commentaires = c.id_commentaires
      WHERE m.nom = :nom
      GROUP BY c.id_commentaires"
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

  function getClassementMonsters(PDO $pdo, string $type = 'commentaires', string $periode = 'mois') {
    $dates = [
      'jour' => "CURDATE()",
      'semaine' => "DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)",
      'mois' => "DATE_FORMAT(CURDATE(), '%Y-%m-01')"
    ];

    if (!isset($dates[$periode])) {
      $periode = 'mois';
    }

    $dateCondition = $dates[$periode];

    if ($type === 'commentaires') {
      $sql = "
        SELECT m.id_monsters, m.nom, m.image, COUNT(c.id_commentaires) AS total
        FROM monsters m
        INNER JOIN commentaires c 
          ON c.id_monsters = m.id_monsters
          AND c.date >= {$dateCondition}
        GROUP BY m.id_monsters
        ORDER BY total DESC
        LIMIT 10
      ";
    } elseif ($type === 'vues') {
      $sql = "
        SELECT m.id_monsters, m.nom, m.image, COUNT(v.id_view) AS total
        FROM monsters m
        INNER JOIN monster_views v
          ON v.id_monsters = m.id_monsters
          AND v.date_view >= {$dateCondition}
        GROUP BY m.id_monsters
        ORDER BY total DESC
        LIMIT 10
      ";
    } elseif ($type === 'bus') {
      $sql = "
        SELECT m.id_monsters, m.nom, m.image, COUNT(d.id_drink) AS total
        FROM monsters m
        INNER JOIN monster_drinks d
          ON d.id_monsters = m.id_monsters
          AND d.date_drink >= {$dateCondition}
        GROUP BY m.id_monsters
        ORDER BY total DESC
        LIMIT 10
      ";
    } else {
      $sql = "
        SELECT
          m.id_monsters,
          m.nom,
          m.image,
          ROUND(AVG(n.note), 2) AS total,
          COUNT(n.note) AS nb_notes
        FROM monsters m
        INNER JOIN notes n
          ON n.id_monsters = m.id_monsters
          AND n.date_note >= {$dateCondition}
        GROUP BY m.id_monsters
        ORDER BY total DESC, nb_notes DESC
        LIMIT 10
      ";
    }

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
?>