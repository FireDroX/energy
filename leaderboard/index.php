<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';
require_once __DIR__ . '/../utils/database.php'
;
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

$typeAffichage = $_GET['type'] ?? 'commentaires';
$periodeAffichage = $_GET['periode'] ?? 'mois';

$classement = getClassementMonsters($pdo, $typeAffichage, $periodeAffichage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monster | Classement</title>

  <link rel="shortcut icon" href="/favicon.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/leaderboard/styles.css">
  <link rel="stylesheet" href="/styles/home.css">
</head>

<body class="monster-lb-page">

  <header>
    <?php require_once __DIR__ . '/../components/navbar.php'; ?>
  </header>
  <?php require_once __DIR__ . '/../components/alert.php'; ?>

<div class="container py-5 monster-lb-container">

  <h1 class="text-center mb-4 monster-lb-title">Classement <span>Monster</span></h1>

  <form method="GET" class="monster-lb-filter p-4 mb-5">
    <div class="row g-3">
      <div class="col-md-5">
        <label class="monster-lb-label">Trier par</label>
        <select name="type" class="monster-lb-select">
          <option value="commentaires" <?= $typeAffichage === 'commentaires' ? 'selected' : '' ?>>Plus commentées</option>
          <option value="notes" <?= $typeAffichage === 'notes' ? 'selected' : '' ?>>Mieux notées</option>
          <option value="vues" <?= $typeAffichage === 'vues' ? 'selected' : '' ?>>Plus vues</option>
          <option value="bus" <?= $typeAffichage === 'bus' ? 'selected' : '' ?>>Plus bues</option>
        </select>
      </div>

      <div class="col-md-5">
        <label class="monster-lb-label">Période</label>
        <select name="periode" class="monster-lb-select">
          <option value="jour" <?= $periodeAffichage === 'jour' ? 'selected' : '' ?>>Aujourd'hui</option>
          <option value="semaine" <?= $periodeAffichage === 'semaine' ? 'selected' : '' ?>>Cette semaine</option>
          <option value="mois" <?= $periodeAffichage === 'mois' ? 'selected' : '' ?>>Ce mois</option>
        </select>
      </div>

      <div class="col-md-2 d-flex align-items-end">
        <button class="monster-lb-filter-btn w-100">Filtrer</button>
      </div>
    </div>
  </form>

  <div class="row g-4">
    <?php foreach ($classement as $i => $monster): ?>
      <div class="col-md-6">
        <div
          class="monster-lb-card"
          data-monster-name="<?= $monster['nom']; ?>"
        >
          <div class="monster-lb-card-body">

            <div class="monster-lb-rank">
              #<?= $i + 1 ?>
            </div>

            <img
              src="<?= htmlspecialchars($monster['image']) ?>"
              class="monster-lb-img"
              alt="<?= htmlspecialchars($monster['nom']) ?>"
            >

            <div class="flex-grow-1">
                <h4 class="monster-lb-name mb-1">
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $monster['nom']))) ?>
                </h4>

                <p class="monster-lb-stat mb-0">
                    <?php if ($typeAffichage === 'notes'): ?>
                        ⭐ <?= $monster['total'] ?>/5
                        <span class="monster-lb-stat-muted">(<?= $monster['nb_notes'] ?> vote(s))</span>

                    <?php elseif ($typeAffichage === 'commentaires'): ?>
                        💬 <?= $monster['total'] ?> commentaire(s)

                    <?php elseif ($typeAffichage === 'vues'): ?>
                        👁️ <?= $monster['total'] ?> vue(s)

                    <?php elseif ($typeAffichage === 'bus'): ?>
                        🥤 <?= $monster['total'] ?> consommation(s)

                    <?php endif; ?>
                </p>
            </div>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>
<?php require_once __DIR__ . '/../components/messages.php'; ?>
</body>
<script defer>
  const cards = document.querySelectorAll(".monster-lb-card");

  cards.forEach((card) => {
    card.addEventListener("click", () => {
      location.href = `/monster/?name=${card.dataset.monsterName}`;
    })
  })
</script>
</html>