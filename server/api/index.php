<?php

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($url, '/api')) {
  header('Content-Type: application/json');

  switch ($url) {
    case '/api/health':
      require __DIR__ . '/health.php';
      break;
    default:
      http_response_code(404);
      echo json_encode(['error' => 'Not Found']);
      break;
  }
  
  exit;
}