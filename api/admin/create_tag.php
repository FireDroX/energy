<?php
require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/loggers.php'; 

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
$name = isset($data['name']) ? trim($data['name']) : '';

if (!$name) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_params']);
    exit;
}

try { 
    $stmt = $pdo->prepare("SELECT nom FROM tags WHERE nom = :nom");
    $stmt->execute([':nom' => $name]);

    if ($stmt->fetch()) {
        echo json_encode(['warning' => 'tag_exists']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO tags (nom) VALUES (:nom)");
    $stmt->execute([':nom' => $name]);

    echo json_encode([
        'success'  => true,
        'message' => 'tag_created'
    ]);
    addLog($pdo, $_SESSION['user']['id'], 'TAG', 'Création de : ' . $name);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'database_error',
        'details' => $e->getMessage()
    ]);
}