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
              <li data-bs-toggle="modal" data-bs-target="#addTagModal">
                <button type="button">
                  +
                </button>
              </li>
            </ul>
          </div>
          <button type="submit" class="btn btn-primary">Sauvegarder</button>
        </form>
      </div>
      <div class="modal fade" id="addTagModal" tabindex="-1" aria-labelledby="addTagModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content panel-card">
            <div class="modal-header">
              <h5 class="modal-title" id="addTagModalLabel">Ajouter un nouveau tag</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
              <label for="newTagName" class="form-label">Nom du tag</label>
              <input type="text" class="form-control" id="newTagName" placeholder="Ex: Original, Ultra, Rehab...">
              <div id="tagModalError" class="text-danger mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="button" class="btn btn-primary" id="saveNewTag">Sauvegarder</button>
            </div>
          </div>
        </div>
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
        const name = tag.textContent.trim();
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

    const addTagModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addTagModal'));
    const newTagInput = document.getElementById('newTagName');
    const tagModalError = document.getElementById('tagModalError');

    document.getElementById('saveNewTag').addEventListener('click', async () => {
      const name = newTagInput.value.trim();

      if (!name) {
        tagModalError.textContent = 'Le nom du tag ne peut pas être vide.';
        tagModalError.classList.remove('d-none');
        return;
      }

      tagModalError.classList.add('d-none');

      const res = await fetch('/api/admin/create_tag.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name })
      });

      const json = await res.json();

      if (json.success) {
        location.href = `/panel/tags?success=${encodeURIComponent(json.message)}`;
      } else if (json.warning) {
        location.href = `/panel/tags?warning=${encodeURIComponent(json.warning)}`;
      } else {
        location.href = `/panel/tags?error=${encodeURIComponent(json.error)}`;
      }
    });

    // Reset du modal à la fermeture
    document.getElementById('addTagModal').addEventListener('hidden.bs.modal', () => {
      newTagInput.value = '';
      tagModalError.classList.add('d-none');
    });
  </script>
</body>
</html>

