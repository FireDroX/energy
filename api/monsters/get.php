<?php
require_once __DIR__ . '/../../utils/database.php';

header('Content-Type: application/json');
http_response_code(200);

try {
    $stmt = $pdo->query("
        SELECT 
            m.id_monsters,
            m.nom,
            m.image,
            GROUP_CONCAT(t.nom) as tags,
            ROUND(AVG(n.note), 2) as note
        FROM monsters m
        LEFT JOIN monster_tags mt ON m.id_monsters = mt.id_monsters
        LEFT JOIN tags t ON mt.id_tags = t.id_tags
        LEFT JOIN notes n ON n.id_monsters = m.id_monsters
        GROUP BY m.id_monsters
        ORDER BY note DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // transformer "Coffee,Java" → ["Coffee", "Java"]
    foreach ($data as &$monster) {
        $monster['tags'] = $monster['tags'] ? explode(',', $monster['tags']) : [];
    }

    echo json_encode($data);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur base de données',
        'message' => $e->getMessage()
    ]);
}