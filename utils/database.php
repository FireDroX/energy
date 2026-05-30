<?php
  require_once __DIR__ . '/functions.php';

  $pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']};charset=utf8",
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD']
  );

  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>