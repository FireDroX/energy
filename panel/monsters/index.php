<?php 
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

if (
    !isset($_SESSION['user']) || 
    ($_SESSION['user']['role'] != 1 && $_SESSION['user']['role'] != 3) || 
    !$_SESSION['user']['is_active']
  ) {
  header("Location: /errors/403.php");
  exit;
}

require_once __DIR__ . '/../../utils/loggers.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);


try {
  $stmt = $pdo->query("SELECT * FROM monsters");
  $monsters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  header("Location: /panel?error=database_error");
}

?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster | Monsters</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../captcha/styles.css">
    <link rel="stylesheet" href="/styles/home.css">
  </head>

  <body>
    <header><?php require_once '../../components/navbar.php'; ?></header>
    <?php require_once '../../components/alert.php' ?>
    <main class="container mt-4">
      <h1>Gestion des monsters</h1>
      <div class="mb-3">
        <label for="monsterSelect" class="form-label">Sélectionnez une monster</label>
        <select class="form-select" id="monsterSelect">
          <option value="" disabled selected>Sélectionnez une monster</option>
          <option value="separator" disabled></option>
          <?php foreach ($monsters as $monster) { ?>
            <option value="<?= $monster['id_monsters'] ?>">
              <?= htmlspecialchars(ucwords(str_replace('_', ' ', $monster['nom']))) ?>
            </option>
          <?php } ?>
          <option value="separator" disabled></option>
          <option value="new">Ajouter un monster</option>
        </select>
      </div>
      <form id="monsterForm">
        <input type="hidden" name="id_monsters" id="id_monsters" value="0">
        <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" class="form-control" id="nom" name="nom">
        </div>
        <div class="mb-3">
          <label class="form-label">URL d'image</label>
          <input type="text" class="form-control" id="image" name="image">
        </div>
        <button type="submit" class="btn btn-primary">
          Sauvegarder
        </button>
      </form>
    </main>
    <script>
      const monsters = <?= json_encode($monsters) ?>;

      const select = document.getElementById('monsterSelect');
      const form = document.getElementById('monsterForm');

      const idInput = document.getElementById('id_monsters');
      const nom = document.getElementById('nom');
      const image = document.getElementById('image');

      select.addEventListener('change', () => {
        const val = select.value;
        if (val === "new") {
          idInput.value = 0;
          nom.value = "Placeholder";
          image.value = "https://example.com/";
          return;
        }
        const monster = monsters.find(c => c.id_monsters == val);
        if (!monster) return;
        idInput.value = monster.id_monsters;
        nom.value = monster.nom;
        image.value = monster.image;
      });

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(form);
        const res = await fetch('/api/admin/monster.php', { method: 'POST', body: data });
        const json = await res.json();
        if (json.success) {
          location.href = `/panel/monsters?success=${encodeURIComponent(json.message)}`;
        } else if (json.warning) {
          location.href = `/panel/monsters?warning=${encodeURIComponent(json.warning)}`;
        } else {
          location.href = `/panel/monsters?error=${encodeURIComponent(json.error)}`;
        }
      });
    </script>
  </body>
</html>