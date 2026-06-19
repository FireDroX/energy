<?php 
require_once __DIR__ . '/../../utils/session.php'; 
require_once __DIR__ . '/../../utils/database.php'; 

if (
    !isset($_SESSION['user']) || 
    $_SESSION['user']['role'] != 1 ||
    !$_SESSION['user']['is_active']
  ) {
  header("Location: /");
  exit;
}

require_once __DIR__ . '/../../utils/loggers.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

try {
  $stmt = $pdo->query("
    SELECT 
    l.id_users,
      l.action,
      l.details,
      l.created_at,
      u.pseudo
    FROM logs l
    INNER JOIN users u ON l.id_users = u.id_users
    ORDER BY l.created_at DESC
  ");
  $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stmt = $pdo->query("SELECT action FROM logs GROUP BY action;");
  $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stmt = $pdo->query("SELECT id_users, pseudo FROM users;");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  header("Location: /panel?error=database_error");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monster | Logs</title>

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
    <h1>Gestion des logs</h1>
    <div class="logs-grid">
      <div class="panel-card">
        <div class="mb-3">
          <label for="typeSelect" class="form-label">
            Sélectionnez une action
          </label>
          <select class="form-select" id="typeSelect">
            <option value="all" selected>
              Toute les actions
            </option>
            <option value="separator" disabled></option>
            <?php foreach ($types as $type) { ?>
            <option value="<?= htmlspecialchars($type['action']) ?>">
              <?= htmlspecialchars($type['action']) ?>
            </option>
            <?php } ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="userSelect" class="form-label">
            Sélectionnez un utilisateur
          </label>
          <select class="form-select" id="userSelect">
            <option value="all" selected>
              Tout les utilisateurs
            </option>
            <option value="separator" disabled></option>
            <?php foreach ($users as &$user) { ?>
            <option value="<?= $user['id_users'] ?>">
              <?= htmlspecialchars($user['pseudo']) ?>
            </option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="panel-card">
        <h2>Logs</h2>
        <ul id="logs-list"></ul>
      </div>
    </div>
  </main>
  <script defer>
    const logs = <?= json_encode($logs) ?>;
    const users = <?= json_encode($users) ?>;
    const types = <?= json_encode($types) ?>;

    const logsList = document.getElementById("logs-list");
    const typeSelect = document.getElementById("typeSelect");
    const userSelect = document.getElementById("userSelect");

    let newLogs = [...logs];

    function showLogs() {
      logsList.innerHTML = "";
      newLogs.forEach((log) => {
        const el = document.createElement("li");

        const date = new Date(log.created_at);
        const formattedDate = date.toLocaleString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });

        el.className = "log"
        el.innerHTML = `
          <div class="log-card">
            <div class="log-header">
              <span class="log-action">${log.action}</span>
              <span class="log-date">${formattedDate}</span>
            </div>
            <div class="log-body">
              <span class="log-user">- ${log.pseudo}</span>
              <span class="log-details">${log.details}</span>
            </div>
          </div>
        `;

        logsList.appendChild(el);
      })
    }

    function getFilters() {
      const action = typeSelect.value;
      const userValue = userSelect.value;
      const pseudoID = userValue === "all" ? "all" : Number(userValue);
      return { action, pseudoID };
    }

    function filterLogs() {
      const { action, pseudoID } = getFilters();
      newLogs = logs.filter((l) => {
        const matchAction = action === "all" || l.action === action;
        const matchUser = pseudoID === "all" || l.id_users === pseudoID;
        return matchAction && matchUser;
      });
      showLogs();
    }

    typeSelect.addEventListener("change", filterLogs);
    userSelect.addEventListener("change", filterLogs);

    showLogs();
  </script>
</body>
</html>

