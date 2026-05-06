<?php
  require_once __DIR__ . '/../../utils/functions.php';

  header('Content-Type: application/json');
  http_response_code(200);

  $mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], false, $_ENV['DB_NAME'], $_ENV['DB_PORT']);
  $result = $mysqli->query("SELECT * FROM `tags`");
  $data = $result->fetch_all(MYSQLI_ASSOC);

  echo json_encode($data);
?>