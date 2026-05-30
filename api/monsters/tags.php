<?php
require_once __DIR__ . '/../../utils/database.php';

header('Content-Type: application/json');
http_response_code(200);

try {
    $stmt = $pdo->query("SELECT * FROM tags");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur base de données',
        'message' => $e->getMessage()
    ]);
}