<?php
    require_once __DIR__ . '/functions.php';

    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, false, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }

?>