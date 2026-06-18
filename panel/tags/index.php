<?php 
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

if (
    !isset($_SESSION['user']) || 
    ($_SESSION['user']['role'] != 1 && $_SESSION['user']['role'] != 3) || 
    !$_SESSION['user']['is_active']
  ) {
  header("Location: /");
  exit;
}

try {
  $stmt = $pdo->query("
    SELECT 
      m.id_monsters,
      m.nom,
      m.image,
      GROUP_CONCAT(t.nom) as tags
    FROM monsters m
    LEFT JOIN monster_tags mt ON m.id_monsters = mt.id_monsters
    LEFT JOIN tags t ON mt.id_tags = t.id_tags
    GROUP BY m.id_monsters
  ");

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($data as &$monster) {
      $monster['tags'] = $monster['tags'] ? explode(',', $monster['tags']) : [];
  }

  $stmt = $pdo->query("SELECT * FROM tags");
  $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  header("Location: /panel?error=database_error");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monster | Tags</title>

  <link rel="shortcut icon" href="/favicon.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="/styles/home.css">
</head>
<body>
  <header> <?php require_once '../../components/navbar.php'; ?></header>
  <?php require_once '../../components/alert.php' ?>
  <main class="container mt-4">
    <h1>Gestion des tags</h1>
    <div class="tags-grid">
      <div class="panel-card">
        <div class="mb-3">
          <label for="monsterSelect" class="form-label">Sélectionnez une monster</label>
          <select class="form-select" id="monsterSelect">
              <option value="" disabled selected>Sélectionnez une monster</option>
              <option value="separator" disabled></option>
              <?php foreach ($data as &$monster) { ?>
                <option value="<?= $monster['id_monsters'] ?>">
                  <?= htmlspecialchars(ucwords(str_replace('_', ' ', $monster['nom']))) ?>
                </option>
              <?php } ?>
          </select>
        </div>
      </div>
      <div class="panel-card">
        <h2>Tags</h2>
        <form id="tagForm">
          <div class="mb-3">
            <ul id="monstersTags">
              <?php foreach($tags as &$t) { ?>
                <li class="tag" data-tag-id="<?= $t['id_tags'] ?>">
                  <?= $t['nom'] ?>
                </li>
              <?php } ?>
            </ul>
          </div>
          <button type="submit" class="btn btn-primary">Sauvegarder</button>
        </form>
      </div>
    </div>
  </main> 
  <?php 
    $dataById = [];
    foreach ($data as &$monster) {
      $dataById[$monster['id_monsters']] = $monster;
    }
  ?>
  <script defer>
    const select = document.getElementById('monsterSelect');
    const form = document.getElementById('tagForm');

    const tags = document.querySelectorAll(".tag");

    select.addEventListener('change', () => {
      const data = <?= json_encode($dataById) ?>;

      const monster = data[select.value];
      const activeTags = monster.tags;

      tags.forEach((tag) => {
        const name = tag.textContent.trim().replaceAll(" ", "_");
        tag.className = activeTags.includes(name) 
          ? "tag tag-active" 
          : "tag tag-not-active";
      });
    });

    tags.forEach((tag) => {
      tag.addEventListener("click" , () => {
        tag.classList.contains("tag-active") ? 
          tag.className = "tag tag-not-active" : 
          tag.className = "tag tag-active"
      });
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const monsterId = parseInt(select.value);
      if (monsterId == "") return location.href = `/panel/tags?warning=missing_fields`;
      const activeTags = [...document.querySelectorAll(".tag-active")]
        .map(tag => parseInt(tag.dataset.tagId));

      console.log(activeTags);

      const res = await fetch('/api/admin/tags.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          monster_id: monsterId,
          tags: activeTags
        })
      });

      const json = await res.json();
      if (json.success) {
        location.href = `/panel/tags?success=${encodeURIComponent(json.message)}`;
      } else {
        location.href = `/panel/tags?error=${encodeURIComponent(json.error)}`;
      }
    })
  </script>
</body>
</html>

