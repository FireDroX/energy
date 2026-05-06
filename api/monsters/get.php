<?php
require_once __DIR__ . '/../../utils/functions.php';

header('Content-Type: application/json');
http_response_code(200);

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']};charset=utf8",
        $_ENV['DB_USER'],
        ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT 
            m.id_monsters,
            m.nom,
            m.image,
            GROUP_CONCAT(t.nom) as tags
        FROM monsters m
        LEFT JOIN monster_tags mt ON m.id_monsters = mt.id_monsters
        LEFT JOIN tags t ON mt.id_tags = t.id_tags
        GROUP BY m.id_monsters
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