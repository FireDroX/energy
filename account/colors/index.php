<?php 
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../utils/loggers.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

$user = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT color_selected FROM users WHERE id_users = :id;");
$stmt->execute(['id' => $user['id']]);
$selectedColor = $stmt->fetch(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT * FROM easter_eggs;");
$stmt->execute();
$allEasterEggs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
  SELECT e.reward FROM user_eggs ue
  INNER JOIN users u ON ue.id_users = u.id_users
  INNER JOIN easter_eggs e ON e.id_egg = ue.id_egg
  WHERE u.id_users = :id;
");
$stmt->execute(['id' => $user['id']]);
$ownColors = $stmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster | Colors</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="colors.css">
    <link rel="stylesheet" href="/styles/home.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  </head>

  <body>
    <header><?php require_once __DIR__ . '/../../components/navbar.php'; ?></header>
    <?php require_once __DIR__ . '/../../components/alert.php'; ?>

    <main class="container">
      <div class="colors-container">
        <?php foreach ($allEasterEggs as $egg) { ?>
          <?php $unlocked = in_array($egg['reward'], $ownColors); ?>
          <div
            class="color-item <?= $unlocked ? 'unlocked' : 'locked' ?> <?= $selectedColor == $egg['reward'] ? "selected" : "" ?>"
            data-egg-name="<?= htmlspecialchars($egg['nom']) ?>"
            data-egg-reward="<?= htmlspecialchars($egg['reward']) ?>"
          >
            <div class="color-item__preview">
              <span
                class="<?= htmlspecialchars($egg['reward']) ?>"
                data-name="<?= htmlspecialchars($user['pseudo']) ?>"
              ><?= htmlspecialchars($user['pseudo']) ?></span>
              <?php if (!$unlocked) { ?>
                <div class="color-item__lock" aria-hidden="true">🔒</div>
              <?php } ?>
            </div>
            <?php if ($unlocked) { ?>
              <p class="color-item__desc"><?= htmlspecialchars($egg['description']) ?></p>
            <?php } else { ?>
              <p class="color-item__hint"><?= htmlspecialchars($egg['hint']) ?></p>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
    </main>
    <?php require_once __DIR__ . '/../../components/messages.php'; ?>
    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
  </body>
  <script defer>
    const unlockedElement = document.querySelectorAll(".color-item.unlocked");

    unlockedElement.forEach((el) => {
      el.addEventListener("click", async () => {
        const easterEggName = el.dataset.eggName;
        const easterEggReward = el.dataset.eggReward;
        try {
          const request = await fetch("/account/colors/toggle.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              easter_egg_name: easterEggName,
              easter_egg_reward: easterEggReward
            }),
          });

          const data = await request.json();

          if (data.success) {
            location.href = `/account?success=${data.message}`;
          } else if (data.warning) {
            location.href = `/account?warning=${data.message}`;
          }
        } catch (err) {
          console.error(err);
        }
      })
    });
  </script>
</html>