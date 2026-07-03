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
        r.role,
        u.avatar
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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </head>

    <body>
        <header><?php require_once '../components/navbar.php'; ?></header>
        <?php require_once '../components/alert.php' ?>
        <main class="container py-5">
            <div class="account-card">
                <div class="account-header">

                    <div
                        class="account-avatar avatar-upload"
                        data-bs-toggle="modal"
                        data-bs-target="#avatarModal">

                        <?php if (!empty($userData['avatar'])): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($userData['avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($userData['pseudo'], 0, 1)) ?>
                        <?php endif; ?>

                        <div class="avatar-overlay">
                            <i class="fa-solid fa-camera"></i>
                        </div>
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

            <div class="account-download">
                <a href="export_pdf.php" class="btn-download">
                    📄 Télécharger mes informations (PDF)
                </a>
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
            <div class="modal fade" id="avatarModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark text-white">

                        <form action="upload_avatar.php"
                            method="POST"
                            enctype="multipart/form-data">

                            <div class="modal-header">
                                <h5 class="modal-title">Modifier la photo de profil</h5>

                                <button
                                    type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal">
                                </button>
                            </div>

                            <div class="avatar-select">
                                <label for="avatar-upload" class="avatar-file-label">
                                    <img
                                        id="avatar-preview"
                                        src=""
                                        alt="Prévisualisation"
                                        hidden>
                                        <span id="avatar-placeholder"> Cliquez pour choisir une image </span>
                                </label>

                                <input
                                    type="file"
                                    id="avatar-upload"
                                    name="avatar"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    hidden>

                                <p id="selected-file">
                                    Aucun fichier sélectionné.
                                </p>
                            </div>

                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="avatar-btn avatar-btn-cancel"
                                    data-bs-dismiss="modal">
                                    Annuler
                                </button>

                                <button
                                    type="submit"
                                    class="avatar-btn avatar-btn-save">
                                    Enregistrer
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </main>

    <?php require_once __DIR__ . '/../components/messages.php'; ?>
    </body>
    <script src="app.js" defer></script>
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