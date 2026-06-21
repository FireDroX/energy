<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../components/alert.php';

require_once __DIR__ . '/../utils/loggers.php';
require_once __DIR__ . '/../utils/database.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

$type = $_GET['type'] ?? 'commentaires';
$periode = $_GET['periode'] ?? 'mois';

$classement = getClassementMonsters($pdo, $type, $periode);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Monster | Classement</title>

  <link rel="shortcut icon" href="/favicon.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/leaderboard/styles.css">
  <link rel="stylesheet" href="/styles/home.css">
</head>

<body>

  <header>
    <?php require_once __DIR__ . '/../components/navbar.php'; ?>
  </header>
<div class="container py-5">

  <h1 class="text-center mb-4 text-light">Classement Monster</h1>

  <form method="GET" class="card bg-black text-light border-secondary p-4 mb-5">
    <div class="row g-3">
      <div class="col-md-5">
        <label class="form-label">Trier par</label>
            <select name="type" class="form-select">
                <option value="commentaires" <?= $type === 'commentaires' ? 'selected' : '' ?>>Plus commentées</option>
                <option value="notes" <?= $type === 'notes' ? 'selected' : '' ?>>Mieux notées</option>
                <option value="vues" <?= $type === 'vues' ? 'selected' : '' ?>>Plus vues</option>
                <option value="bus" <?= $type === 'bus' ? 'selected' : '' ?>>Plus bues</option>
            </select>
      </div>

      <div class="col-md-5">
        <label class="form-label">Période</label>
        <select name="periode" class="form-select">
          <option value="jour" <?= $periode === 'jour' ? 'selected' : '' ?>>Aujourd'hui</option>
          <option value="semaine" <?= $periode === 'semaine' ? 'selected' : '' ?>>Cette semaine</option>
          <option value="mois" <?= $periode === 'mois' ? 'selected' : '' ?>>Ce mois</option>
        </select>
      </div>

      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-success w-100">Filtrer</button>
      </div>
    </div>
  </form>

  <div class="row g-4">
    <?php foreach ($classement as $i => $monster): ?>
      <div class="col-md-6">
        <div 
          class="card ranking-card bg-black text-light border-secondary shadow"
          data-monster-name="<?= $monster['nom']; ?>"
        >
          <div class="card-body d-flex align-items-center gap-3">
            
            <div class="rank-number text-success">
              #<?= $i + 1 ?>
            </div>

            <img 
              src="<?= htmlspecialchars($monster['image']) ?>" 
              class="ranking-img"
              alt="<?= htmlspecialchars($monster['nom']) ?>"
            >

            <div class="flex-grow-1">
                <h4 class="mb-1">
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $monster['nom']))) ?>
                </h4>

                <p class="mb-0 text-secondary">
                    <?php if ($type === 'notes'): ?>
                        ⭐ <?= $monster['total'] ?>/5
                        (<?= $monster['nb_notes'] ?> vote(s))

                    <?php elseif ($type === 'commentaires'): ?>
                        💬 <?= $monster['total'] ?> commentaire(s)

                    <?php elseif ($type === 'vues'): ?>
                        👁️ <?= $monster['total'] ?> vue(s)

                    <?php elseif ($type === 'bus'): ?>
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
  const cards = document.querySelectorAll(".ranking-card");

  cards.forEach((card) => {
    card.addEventListener("click", () => {
      location.href = `/monster/?name=${card.dataset.monsterName}`;
    })
  })
</script>
</html>