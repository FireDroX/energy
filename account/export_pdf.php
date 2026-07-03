<?php

session_start();

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';
require_once '../vendor/autoload.php';
require_once '../utils/database.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user'])) {
    exit('Non autorisé');
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT
        u.id_users,
        u.pseudo,
        u.mail,
        u.created,
        u.newsletter,
        r.role
    FROM users u
    INNER JOIN roles r
        ON r.id_role = u.id_role
    WHERE u.id_users = ?
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        background: #ffffff;
        color: #111111;
        font-size: 12px;
        margin: 0;
        padding: 0;
    }

    .page {
        padding: 28px;
    }

    .header {
        background: #0d0d0d;
        color: #ffffff;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 22px;
    }

    .title {
        font-size: 28px;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0 0 6px 0;
    }

    .subtitle {
        color: #bfbfbf;
        margin: 0;
    }

    .section {
        margin-top: 20px;
    }

    .section-title {
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #111111;
        padding-bottom: 6px;
        margin-bottom: 12px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table th,
    .info-table td {
        border: 1px solid #d0d0d0;
        padding: 8px;
        text-align: left;
    }

    .info-table th {
        width: 35%;
        background: #111111;
        color: #ffffff;
    }

    .footer {
        margin-top: 28px;
        padding-top: 12px;
        border-top: 1px solid #d0d0d0;
        color: #777777;
        font-size: 10px;
    }
</style>
</head>

<body>

<div class="page">

    <div class="header">
        <h1 class="title">Informations personnelles</h1>
        <p class="subtitle">Export généré le ' . date('d/m/Y à H:i') . '</p>
    </div>

    <div class="section">
        <h2 class="section-title">Compte utilisateur</h2>

        <table class="info-table">
            <tr>
                <th>ID</th>
                <td>' . htmlspecialchars($user['id_users']) . '</td>
            </tr>

            <tr>
                <th>Pseudo</th>
                <td>' . htmlspecialchars($user['pseudo']) . '</td>
            </tr>

            <tr>
                <th>Email</th>
                <td>' . htmlspecialchars($user['mail']) . '</td>
            </tr>

            <tr>
                <th>Rôle</th>
                <td>' . htmlspecialchars($user['role']) . '</td>
            </tr>

            <tr>
                <th>Date d\'inscription</th>
                <td>' . htmlspecialchars($user['created']) . '</td>
            </tr>

            <tr>
                <th>Newsletter</th>
                <td>' . ($user['newsletter'] ? 'Oui' : 'Non') . '</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Export généré par ' . htmlspecialchars($user['pseudo']) . ' depuis Monster Energy - Review.
    </div>

</div>

</body>
</html>
';

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

addLog(
    $pdo,
    $userId,
    'EXPORT_PDF',
    $user['pseudo'] . ' Télécharge ses informations en PDF'
);

$pseudo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user['pseudo']);

$dompdf->stream(
    'Informations - ' . $pseudo . '.pdf',
    [
        'Attachment' => true
    ]
);
?>