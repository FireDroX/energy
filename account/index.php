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
        u.avatar,
        u.color_selected
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

        <link rel="stylesheet" href="colors/colors.css">
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
                            <h1>
                                <span
                                    data-name="<?= htmlspecialchars($userData['pseudo']) ?>"
                                    class="<?= htmlspecialchars($userData['color_selected']) ?>"
                                >
                                    <?= htmlspecialchars($userData['pseudo']) ?>
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="change-color" viewBox="0 0 512 512"><path d="M441,336.2l-.06-.05c-9.93-9.18-22.78-11.34-32.16-12.92l-.69-.12c-9.05-1.49-10.48-2.5-14.58-6.17-2.44-2.17-5.35-5.65-5.35-9.94s2.91-7.77,5.34-9.94l30.28-26.87c25.92-22.91,40.2-53.66,40.2-86.59S449.73,119.92,423.78,97c-35.89-31.59-85-49-138.37-49C223.72,48,162,71.37,116,112.11c-43.87,38.77-68,90.71-68,146.24s24.16,107.47,68,146.23c21.75,19.24,47.49,34.18,76.52,44.42a266.17,266.17,0,0,0,86.87,15h1.81c61,0,119.09-20.57,159.39-56.4,9.7-8.56,15.15-20.83,15.34-34.56C456.14,358.87,450.56,345.09,441,336.2ZM112,208a32,32,0,1,1,32,32A32,32,0,0,1,112,208Zm40,135a32,32,0,1,1,32-32A32,32,0,0,1,152,343Zm40-199a32,32,0,1,1,32,32A32,32,0,0,1,192,144Zm64,271a48,48,0,1,1,48-48A48,48,0,0,1,256,415Zm72-239a32,32,0,1,1,32-32A32,32,0,0,1,328,176Z"/></svg>
                            </h1>
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