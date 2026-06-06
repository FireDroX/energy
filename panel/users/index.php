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

try {
  $stmt = $pdo->query("
    SELECT 
      u.id_users,
      u.pseudo,
      u.mail,
      u.mdp,
      u.id_role,
      u.deactivated
    FROM users u
    ORDER BY u.created DESC;
  ");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $stmt = $pdo->query("SELECT * FROM roles");
  $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $users = [[
    'id_users' => 0,
    'pseudo' => 'Pseudo',
    'mail' => 'mail@example.com',
    'mdp' => 'hashed_password',
    'id_role' => 1,
    'role' => 'User'
  ]];
}
?>

<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Monster | Users</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png" />

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <header><?php require_once '../../components/navbar.php'; ?></header>
    <?php require_once '../../components/alert.php' ?>
    <main class="container-fluid admin-users">
      <h1>Gestion des utilisateurs</h1>
      <div class="user-grid">
        <div class="panel-card">
          <h2>Utilisateur</h2>
          <div class="mb-3">
            <label for="userSelect" class="form-label">
              Sélectionnez un utilisateur
            </label>
            <select class="form-select" id="userSelect">
              <option value="" disabled selected>
                Sélectionnez un utilisateur
              </option>
              <option value="separator" disabled></option>
              <?php foreach ($users as $user) { ?>
              <option value="<?= $user['id_users'] ?>">
                <?= htmlspecialchars($user['pseudo']) ?>
              </option>
              <?php } ?>
              <option value="separator" disabled></option>
              <option value="new">Ajouter un utilisateur</option>
            </select>
          </div>
        </div>
        <div class="panel-card">
          <h2>Informations</h2>
          <form id="userForm">
            <input type="hidden" name="id_user" id="id_user" value="0" />
            <div class="mb-3">
              <label class="form-label">Pseudo</label>
              <input
                type="text"
                class="form-control"
                id="pseudo"
                name="pseudo"
              />
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="mail" name="mail" />
            </div>
            <div class="mb-3">
              <label class="form-label">Mot de passe hashé</label>
              <input type="text" class="form-control" id="mdp" name="mdp" />
            </div>
            <div class="mb-3">
              <label class="form-label">Rôle</label>
              <select class="form-select" id="id_role" name="id_role">
                <option value="">Sélectionnez un rôle</option>
                <?php foreach ($roles as $role) { ?>
                <option value="<?= $role['id_role'] ?>">
                  <?= htmlspecialchars($role['role']) ?>
                </option>
                <?php } ?>
              </select>
            </div>
            <div class="status-switch">
              <span>Désactivé</span>
              <label class="switch">
                <input type="checkbox" id="active" name="active" />
                <span class="slider"></span>
              </label>
              <span>Actif</span>
            </div>
            <button type="submit" class="btn btn-primary">Sauvegarder</button>
          </form>
        </div>
      </div>
    </main>
    <script>
      const users = <?= json_encode($users) ?>;

      const select = document.getElementById('userSelect');
      const form = document.getElementById('userForm');

      const idInput = document.getElementById('id_user');
      const pseudo = document.getElementById('pseudo');
      const mail = document.getElementById('mail');
      const mdp = document.getElementById('mdp');
      const id_role = document.getElementById('id_role');
      const slider = document.getElementById('active');

      select.addEventListener('change', () => {
        const val = select.value;
        if (val === "new") {
          idInput.value = 0;
          pseudo.value = "Pseudo";
          mail.value = "mail@example.com";
          mdp.value = "Mot de passe Non Hasher";
          id_role.value = 2;
          return;
        }
        const user = users.find(u => u.id_users == val);
        if(!user) return;
        idInput.value = user.id_users;
        pseudo.value = user.pseudo;
        mail.value = user.mail;
        mdp.value = user.mdp
        id_role.value = user.id_role;
        slider.checked = user.deactivated == null;
      });

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(form);
        
        const active = document.getElementById("active").checked ? 1 : 0;
        data.append("active", active);

        const res = await fetch('/api/admin/users.php', { method: 'POST', body: data });
        const json = await res.json();
        if (json.success) {
          location.href = `/panel/users?success=${encodeURIComponent(json.message)}`;
        } else if (json.warning) {
          location.href = `/panel/users?warning=${encodeURIComponent(json.warning)}`;
        } else {
          location.href = `/panel/users?error=${encodeURIComponent(json.error)}`;
        }
      });
    </script>
  </body>
</html>

