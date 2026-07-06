<?php 
require_once __DIR__ . '/utils/session.php'; 

$topMonsters = getClassementMonsters($pdo, 'notes', 'mois');

if (count($topMonsters) < 3) {
  $topMonsters = getClassementMonsters($pdo, 'commentaires', 'mois');
}

$topMonsters = array_slice($topMonsters, 0, 3);
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/home.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/console/terminal/terminal.css">
  </head>

  <body>
    <header>
      <?php require_once __DIR__ . '/components/navbar.php'; ?>
    </header>

    <?php require_once __DIR__ . '/components/alert.php'; ?>

    <main class="home-page">
      <section class="home-top">
        <div class="home-section-header">
          <span id="verdict-secret">Ton Verdict ?</span>
          <h2>Top 3 du mois</h2>
        </div>

        <?php if (empty($topMonsters)): ?>
          <p class="home-empty">Aucun classement disponible pour le moment.</p>
        <?php else: ?>
          <div class="home-podium">
            <?php foreach ($topMonsters as $i => $monster): ?>
              <article class="podium-card podium-<?= $i + 1 ?>" data-monster-name="<?= $monster['nom'] ?>">
                <div class="podium-medal">
                  <?= ['#1', '#2', '#3'][$i] ?>
                </div>

                <img
                  src="<?= htmlspecialchars($monster['image']) ?>"
                  alt="<?= htmlspecialchars($monster['nom']) ?>"
                >

                <h3>
                  <?= htmlspecialchars(ucwords(str_replace('_', ' ', $monster['nom']))) ?>
                </h3>

                <p>
                  <?php if (isset($monster['nb_notes'])): ?>
                    ⭐ <?= $monster['total'] ?>/5
                  <?php else: ?>
                    💬 <?= $monster['total'] ?> commentaire(s)
                  <?php endif; ?>
                </p>

                <?php if (isset($monster['nb_notes'])): ?>
                  <small><?= $monster['nb_notes'] ?> vote(s)</small>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="text-center mt-4">
          <a href="/leaderboard" class="home-see-more">
            Voir le classement complet
          </a>
        </div>
      </section>
    </main>

    <div id="terminal-modal" class="terminal-modal">

        <div class="terminal-window">

            <div class="terminal-header">
                <span class="dot red"></span>
                <span class="dot yellow"></span>
                <span class="dot green"></span>

                <span class="terminal-title">
                    MonsterOS - v1.0
                </span>
            </div>

            <div id="terminal-output"></div>

            <div class="terminal-input">

                <?php
                $username = isset($_SESSION['user'])
                    ? htmlspecialchars($_SESSION['user']['pseudo'])
                    : 'guest';
                ?>

                <span class="prompt">
                    <?= $username ?>@monster:~$
                </span>

                <input
                    id="terminal-command"
                    type="text"
                    autocomplete="off"
                    spellcheck="false"
                >

            </div>

        </div>
    </div>

    <div id="charging-screen" class="charging-screen">
        <div class="charging-content">

            <h1>CHARGING CAFFEINE...</h1>

            <div id="charging-timer">
                05:00
            </div>

            <div id="charging-progress">
                <div id="charging-progress-bar"></div>
            </div>

            <div id="charging-percent">
                0%
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/messages.php'; ?>
    <?php require_once __DIR__ . '/components/footer.php'; ?>
  </body>
  <script defer>
    const cards = document.querySelectorAll(".podium-card");

    cards.forEach((card) => {
      card.addEventListener("click", () => {
        location.href = `/monster/?name=${card.dataset.monsterName}`;
      })
    })
  </script>
  <script src="/console/terminal/terminal.js"></script>
</html>