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
            GROUP_CONCAT(t.nom) as tags,
            ROUND(AVG(n.note), 2) as note,
            CASE
                WHEN mf.id_users IS NULL THEN 0
                ELSE 1
            END AS favorite
        FROM monsters m
        LEFT JOIN monster_tags mt ON m.id_monsters = mt.id_monsters
        LEFT JOIN tags t ON mt.id_tags = t.id_tags
        LEFT JOIN notes n ON n.id_monsters = m.id_monsters
        LEFT JOIN monster_favorites mf
            ON mf.id_monsters = m.id_monsters
            AND mf.id_users = :userId
        GROUP BY m.id_monsters
        ORDER BY note DESC
    ");

    $stmt->execute([
    'userId' => $userId
    ]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$monster) {
        $monster['tags'] = $monster['tags'] ? explode(',', $monster['tags']) : [];
        $monster['favorite'] = (bool)$monster['favorite'];
    }

    echo json_encode($data);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur base de données',
        'message' => $e->getMessage()
    ]);
}