<?php
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/session.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

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
        <title>Mon Compte</title>

        <link rel="stylesheet" href="styles.css">

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >
    </head>

    <body>
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

                <div class="account-actions">
                    <a href="/" class="btn-main">
                        Retour à l'accueil
                    </a>
                    <a href="/logout" class="btn-secondary">
                        Déconnexion
                    </a>
                </div>

            </div>
        </main>
    </body>
</html>