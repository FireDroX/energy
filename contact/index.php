<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';
require_once __DIR__ . '/../utils/database.php';
$gitStats = require_once __DIR__ . '/../utils/git-stats.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monster | Contact</title>

  <link rel="shortcut icon" href="/favicon.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/styles/home.css">
  <link rel="stylesheet" href="styles.css">
</head>

<body class="">

  <header><?php require_once __DIR__ . '/../components/navbar.php'; ?></header>
  <?php require_once __DIR__ . '/../components/alert.php'; ?>

  <main class="container">
    <div class="home-section-header">
      <span id="verdict-secret">les meilleurs</span>
      <h2>Les devs</h2>
    </div>
    <div class="cards">
      <a href="https://github.com/FireDroX" target="_blank" rel="noopener noreferrer">
        <img src="https://gitfut.com/FireDroX.png" alt="Adrien" height="300px" />
      </a>
      <a href="https://github.com/XeTr0S" target="_blank" rel="noopener noreferrer">
        <img src="https://gitfut.com/XeTr0S.png?country=KH" alt="Hassrol" height="300px" />
      </a>
    </div>

    <section class="dev-stats">
        <div class="stat-circle">
            <div class="circle circle-green">
                <span><?= number_format($gitStats['commits']) ?></span>
            </div>

            <h4>Commits</h4>
        </div>

        <div class="stat-circle">
            <div class="circle circle-blue">
                <span><?= number_format($gitStats['added']) ?></span>
            </div>

            <h4>Lignes ajoutées</h4>
        </div>

        <div class="stat-circle">
            <div class="circle circle-red">
                <span><?= number_format($gitStats['deleted']) ?></span>
            </div>

            <h4>Lignes supprimées</h4>
        </div>

        <div class="stat-circle">
            <div class="circle circle-orange">
                <span>TO DO</span>
            </div>

            <h4>Monster achetées</h4>
        </div>

    </section>

  </main>

  <?php require_once __DIR__ . '/../components/messages.php'; ?>
  <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>