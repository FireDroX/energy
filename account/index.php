<?php
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/session.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../utils/loggers.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

$user = $_SESSION['user'];

$stmt = $pdo->prepare("
    SELECT 
        u.id_users,
        u.pseudo,
        u.mail,
        u.mdp,
        u.created,
        u.newsletter, 
        r.role
    FROM users u 
    INNER JOIN roles r ON r.id_role = u.id_role
    WHERE u.id_users = :id;
");

$stmt->execute(['id' => $user['id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Monster | Mon Compte</title>

        <link rel="shortcut icon" href="/favicon.png" type="image/png">

        <link rel="stylesheet" href="styles.css">

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >
    </head>

    <body>
        <header><?php require_once '../components/navbar.php'; ?></header>
        <?php require_once '../components/alert.php' ?>
        <main class="container py-5">
            <div class="account-card">
                <div class="account-header">
                    <div class="account-avatar">
                        <?= strtoupper(substr($userData['pseudo'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1><?= htmlspecialchars($userData['pseudo']) ?></h1>
                        <p class="account-role">
                            <?= htmlspecialchars($userData['role']) ?>
                        </p>
                    </div>
                </div>

                <div class="account-stats">
                    <div class="stat-box">
                        <span class="stat-label">
                            ID UTILISATEUR
                        </span>
                        <span class="stat-value">
                            <?= htmlspecialchars($userData['id_users']) ?>
                        </span>
                    </div>

                    <div class="stat-box">
                        <span class="stat-label">
                            EMAIL
                        </span>
                        <span class="stat-value">
                            <?= htmlspecialchars($userData['mail']) ?>
                        </span>
                    </div>

                    <div class="stat-box">
                        <span class="stat-label">
                            RÔLE
                        </span>
                        <span class="stat-value">
                            <?= htmlspecialchars($userData['role']) ?>
                        </span>
                    </div>
                </div>

                <div class="account-footer">
                    <div class="account-actions">
                        <a href="/" class="btn-main">
                            Retour à l'accueil
                        </a>
                        <a href="/logout" class="btn-secondary">
                            Déconnexion
                        </a>
                    </div>
                    <div class="status-switch">
                        <span>Newsletter (NON / OUI)</span>
                        <label class="switch">
                            <input type="checkbox" id="active" name="active" />
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </main>
    </body>
    <script defer>
        const user = <?= json_encode($userData) ?>;
        const slider = document.getElementById('active');

        slider.checked = user.newsletter == 1;
        slider.addEventListener("change", async () => {
            const data = new FormData();
            const value = slider.checked == true ? 1 : 0;
            data.append("value", value)
            const res = await fetch('/api/account/newsletter.php', { method: 'POST', body: data});
            const json = await res.json();
            if (json.success) {
                location.href = `/account?success=${encodeURIComponent(json.message)}`;
            } else if (json.warning) {
                location.href = `/account?warning=${encodeURIComponent(json.warning)}`;
            } else {
                location.href = `/account?error=${encodeURIComponent(json.error)}`;
            }
        })
    </script>
</html>