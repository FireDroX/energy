<?php 
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

if (
    !isset($_SESSION['user']) || 
    $_SESSION['user']['role'] != 1 ||
    !$_SESSION['user']['is_active']
  ) {
  header("Location: /errors/403.php");
  exit;
}

require_once __DIR__ . '/../../utils/loggers.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);


try {
  $stmt = $pdo->query("SELECT * FROM captcha");
  $captchas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $captchas = [[
    'id_captcha' => 0,
    'question' => 'Quels sont les 2 VRAI délégués de la classe ?',
    'reponse' => '["hassrol","adrien"]'
  ]];
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster | Captcha</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="/styles/home.css">
  </head>

  <body>
    <header><?php require_once '../../components/navbar.php'; ?></header>
    <?php require_once '../../components/alert.php' ?>
    <main class="container mt-4">
      <h1>Gestion des captchas</h1>
      <div class="mb-3">
        <label for="captchaSelect" class="form-label">Sélectionnez un captcha</label>
        <select class="form-select" id="captchaSelect">
          <option value="" disabled selected>Sélectionnez un captcha</option>
          <option value="separator" disabled></option>
          <?php foreach ($captchas as $captcha) { ?>
            <option value="<?= $captcha['id_captcha'] ?>">
              <?= htmlspecialchars($captcha['question']) ?>
            </option>
          <?php } ?>
          <option value="separator" disabled></option>
          <option value="new">Ajouter un captcha</option>
        </select>
      </div>
      <form id="captchaForm">
        <input type="hidden" name="id_captcha" id="id_captcha" value="0">
        <div class="mb-3">
          <label class="form-label">Question</label>
          <input type="text" class="form-control" id="question" name="question">
        </div>
        <div class="mb-3">
          <label class="form-label">Réponse (JSON array)</label>
          <input type="text" class="form-control" id="reponse" name="reponse">
        </div>
        <button type="submit" class="btn btn-primary">
          Sauvegarder
        </button>
      </form>
    </main>
    <script>
      const captchas = <?= json_encode($captchas) ?>;

      const select = document.getElementById('captchaSelect');
      const form = document.getElementById('captchaForm');

      const idInput = document.getElementById('id_captcha');
      const question = document.getElementById('question');
      const reponse = document.getElementById('reponse');

      select.addEventListener('change', () => {
        const val = select.value;
        if (val === "new") {
          idInput.value = 0;
          question.value = "Quels sont les 2 VRAI délégués de la classe ?";
          reponse.value = "[\"hassrol\",\"adrien\"]";
          return;
        }
        const captcha = captchas.find(c => c.id_captcha == val);
        if (!captcha) return;
        idInput.value = captcha.id_captcha;
        question.value = captcha.question;
        reponse.value = captcha.reponse;
      });

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(form);
        const res = await fetch('/api/admin/captcha.php', { method: 'POST', body: data });
        const json = await res.json();
        if (json.success) {
          location.href = `/panel/captcha?success=${encodeURIComponent(json.message)}`;
        } else if (json.warning) {
          location.href = `/panel/captcha?warning=${encodeURIComponent(json.warning)}`;
        } else {
          location.href = `/panel/captcha?error=${encodeURIComponent(json.error)}`;
        }
      });
    </script>
  </body>
</html>