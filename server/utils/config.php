<?php

// DATABASE CONFIG (db + tables)

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, false, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo->exec("USE `$db`");
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}