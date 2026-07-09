<?php 
require_once __DIR__ . '/../../utils/session.php'; 

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $deleteId = (int) $_POST['delete_id'];
  try {
    $del = $pdo->prepare("DELETE FROM messages WHERE id_messages = :id");
    $del->execute([':id' => $deleteId]);
    header("Location: /panel/moderation?deleted=1");
    exit;
  } catch (PDOException $e) {
    header("Location: /panel/moderation?error=delete_failed");
    exit;
  }
}

try {
  $stmt = $pdo->query("
    SELECT 
      id_messages, contenu, date_envoie
    FROM messages
    ORDER BY date_envoie DESC;
  ");
  $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  header("Location: /panel?error=database_error");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monster | Moderation</title>

  <link rel="shortcut icon" href="/favicon.png" type="image/png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="/styles/home.css">
</head>
<body>
  <header>
    <?php require_once '../../components/navbar.php'; ?>
  </header>
  <main class="container mt-4">
    <h1>Gestion de la moderation</h1>
    <div class="msgs-grid">
      <ul id="msgs-list"></ul>
    </div>
  </main>
  <script defer>
    const msgs = <?= json_encode($msgs) ?>;

    const msgsList = document.getElementById("msgs-list");
    
    function showMsgs() {
      msgsList.innerHTML = "";
      msgs.forEach((msg) => {
        const el = document.createElement("li");

        const date = new Date(msg.date_envoie);
        const formattedDate = date.toLocaleString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });

        el.className = "msg";
        el.innerHTML = `
          <div class="msg-card">
            <div class="msg-header">
              <div class="msg-info">
                <span class="msg-action">${msg.contenu}</span>
                <span class="msg-date">${formattedDate}</span>
              </div>
              <form method="post" class="delete-form" onsubmit="return confirm('Supprimer ce message ?');">
                <input type="hidden" name="delete_id" value="${msg.id_messages}">
                <button type="submit" class="btn btn-sm btn-danger delete-btn">Supprimer</button>
              </form>
            </div>
          </div>
        `;

        msgsList.appendChild(el);
      })
    }

    showMsgs();
  </script>
</body>
</html>

