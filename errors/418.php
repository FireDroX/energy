<?php require_once __DIR__ . '/../utils/session.php'; ?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster | 418</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/errors.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  </head>

  <body>
    <header><?php require_once __DIR__ . '/../components/navbar.php'; ?></header>
    <?php require_once __DIR__ . '/../components/alert.php'; ?>
    <main class="errors-page">
      <a href="https://http.cat/" target="_blank" rel="noopener noreferrer"><img src="https://http.cat/418" alt="cat sans cat"></a>
    </main>
    <footer></footer>
  </body>
</html>