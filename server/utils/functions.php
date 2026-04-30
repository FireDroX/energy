<?php 

function loadEnv($path) {
  $lines = file($path);

  foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || $line[0] === '#') continue;

    $parts = explode('=', $line);

    $key = $parts[0];
    $value = $parts[1];

    $_ENV[$key] = $value;
  }
}
loadEnv(__DIR__ . '/../.env');
