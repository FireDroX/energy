<?php
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/session.php';

$userId = $_SESSION['user']['id'] ?? 0;

header('Content-Type: application/json');
http_response_code(200);

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.id_monsters,
            m.nom,
            m.image,
            GROUP_CONCAT(DISTINCT t.nom) AS tags,
            ROUND(AVG(n.note), 2) AS note,
            (mf.id_users IS NOT NULL) AS favorite,
            (md.id_users IS NOT NULL) AS drank,
            nu.note AS user_note
        FROM monsters m
        LEFT JOIN monster_tags mt ON m.id_monsters = mt.id_monsters
        LEFT JOIN tags t ON mt.id_tags = t.id_tags
        LEFT JOIN notes n ON n.id_monsters = m.id_monsters
        LEFT JOIN monster_favorites mf
            ON mf.id_monsters = m.id_monsters
            AND mf.id_users = :userId
        LEFT JOIN monster_drinks md
            ON md.id_monsters = m.id_monsters
            AND md.id_users = :userId
            AND md.date_drink >= NOW() - INTERVAL 1 DAY
        LEFT JOIN notes nu
            ON nu.id_monsters = m.id_monsters
            AND nu.id_users = :userId
        GROUP BY m.id_monsters
        ORDER BY note DESC;
    ");

    $stmt->execute([
    'userId' => $userId
    ]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$monster) {
        $monster['tags'] = $monster['tags'] ? explode(',', $monster['tags']) : [];
        $monster['favorite'] = (bool)$monster['favorite'];
        $monster['drank'] = (bool)$monster['drank'];
    }

    echo json_encode($data);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur base de données',
        'message' => $e->getMessage()
    ]);
}