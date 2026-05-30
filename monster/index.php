<?php 
require_once __DIR__ . '/../utils/session.php'; 
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/functions.php';

$monsterName = $_GET['name'] ?? null;
if ($monsterName === null) goHome();

$monster = getMonster($pdo, $monsterName);
if ($monster === null) goHome();

$comments = getComments($pdo, $monsterName);
$parents = [];
$reponses = [];

foreach ($comments as $comment) {
  if ($comment['id_parent'] === null) {
    $parents[] = $comment;
  } else {
    $reponses[$comment['id_parent']][] = $comment;
  }
}

usort($parents, function ($a, $b) {
  if ($a['is_pinned'] != $b['is_pinned']) {
    return $b['is_pinned'] <=> $a['is_pinned'];
  }
  return $b['nb_likes'] <=> $a['nb_likes'];
});

foreach ($reponses as &$liste) {
  usort($liste, function ($a, $b) {
    return strtotime($a['date'])
      <=> strtotime($b['date']);
  });
}
unset($liste);

function formatName($name) {
  $name = str_replace('_', ' ', $name);
  return strtoupper($name);
}

function goHome() {
  header('Location: /');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monster | <?= formatName($monsterName); ?></title>

  <link rel="shortcut icon" href="/favicon.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header>
    <?php require_once __DIR__ . '/../components/navbar.php'; ?>
  </header>
  <main class="container">
    <section class="monster-header">
      <img src=<?= $monster['image']; ?> />
      <div>
        <h2><?= formatName($monsterName); ?></h2>
        <ul>
          <?php foreach(explode(",", $monster['tags']) as $tag) { ?>
            <li><?= $tag; ?></li>
          <?php } ?>
        </ul>
        <div class="monster-score">
          <div class="progress">
            <div
              class="progress-bar"
              role="progressbar"
              style="width: <?= ($monster['score'] / 5) * 100; ?>%;"
              aria-valuenow="<?= $monster['score']; ?>"
              aria-valuemin="0"
              aria-valuemax="5"
            ></div>
          </div>
          <small><?= $monster['score']; ?> / 5 - (<?= $monster['nb_notes'] ?> notes) </small>
        </div>
      </div>
    </section>
    <br />
    <section class="monster-commentsList">
      <?php foreach($parents as $comment) { ?>
        <article id="<?= $comment['id_commentaires']; ?>" class="monster-comment <?= $comment['is_pinned'] ? 'comment-pinned' : ''; ?>">
          <div class="">
            <div class="">
              <h6><?= htmlspecialchars($comment['pseudo']); ?>
                <?php if($comment['is_pinned']) { ?>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z" clip-rule="evenodd" /></svg>
                <?php } ?>
              </h6>
            </div>
            <small><?= $comment['nb_likes']; ?> likes</small>
          </div>
          <p><?= htmlspecialchars($comment['commentaire']); ?></p>
        </article>
        <?php if(isset($reponses[$comment['id_commentaires']])) { ?>
          <div class="monster-comment-replies">
            <?php foreach($reponses[$comment['id_commentaires']] as $reply) { ?>
              <div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.49 12 3.75 3.75m0 0-3.75 3.75m3.75-3.75H3.74V4.499" /></svg>
                <article id="<?= $reply['id_commentaires']; ?>" class="monster-comment">
                  <div class="">
                    <h6><?= htmlspecialchars($reply['pseudo']); ?></h6>
                    <small><?= $reply['nb_likes']; ?> likes</small>
                  </div>
                  <p><?= htmlspecialchars($reply['commentaire']); ?></p>
                </article>
              </div>
            <?php } ?>
          </div>
        <?php } ?>
      <?php } ?>
    </section>
  </main>
</body>
</html>

