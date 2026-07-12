<?php
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

$stmt = $pdo->prepare("
    SELECT 
        m.nom,
        COALESCE(ROUND(AVG(n.note), 2), 'Non noté') AS note,
        mf.id_monsters
    FROM monster_favorites mf
    LEFT JOIN monsters m ON m.id_monsters = mf.id_monsters
    LEFT JOIN notes n ON n.id_monsters = m.id_monsters
    WHERE mf.id_users = ?
    GROUP BY mf.id_monsters
    ORDER BY m.nom ASC
");
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        c.commentaire,
        m.nom as monster_name,
        c.date
    FROM commentaires c
    LEFT JOIN monsters m ON m.id_monsters = c.id_monsters
    WHERE c.id_users = ?
    ORDER BY c.date DESC
");
$stmt->execute([$userId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        e.nom,
        e.description
    FROM user_eggs ue
    LEFT JOIN easter_eggs e ON e.id_egg = ue.id_egg
    WHERE ue.id_users = ?
");
$stmt->execute([$userId]);
$eggs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM monster_favorites WHERE id_users = ?) as total_likes,
        (SELECT COUNT(*) FROM notes WHERE id_users = ?) as total_ratings,
        (SELECT COUNT(*) FROM commentaires WHERE id_users = ? AND id_parent IS NULL) as total_comments,
        (SELECT COUNT(*) FROM commentaires WHERE id_users = ? AND id_parent IS NOT NULL) as total_replies,
        (SELECT COUNT(*) FROM monster_drinks WHERE id_users = ?) as total_drank,
        (SELECT COUNT(*) FROM monster_views WHERE id_users = ?) as total_views,
        (SELECT COUNT(*) FROM likes WHERE id_users = ?) as total_comment_likes
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

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
        page-break-inside: avoid;
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
        margin-bottom: 16px;
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

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }

    .stat-box {
        border: 1px solid #d0d0d0;
        padding: 12px;
        border-radius: 6px;
        background: #f9f9f9;
    }

    .stat-label {
        font-size: 10px;
        color: #666666;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 18px;
        font-weight: bold;
        color: #111111;
    }

    .list-section {
        margin-bottom: 16px;
    }

    .item {
        border-left: 3px solid #d0d0d0;
        padding: 8px 0 8px 12px;
        margin-bottom: 8px;
    }

    .item-title {
        font-weight: bold;
        color: #111111;
    }

    .item-subtitle {
        color: #666666;
        font-size: 11px;
    }

    .item-content {
        color: #333333;
        font-size: 11px;
        margin-top: 4px;
    }

    .empty-section {
        color: #999999;
        font-style: italic;
    }

    .footer {
        margin-top: 28px;
        padding-top: 12px;
        border-top: 1px solid #d0d0d0;
        color: #777777;
        font-size: 10px;
    }

    .page-break {
        page-break-after: always;
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

    <div class="section">
        <h2 class="section-title">Statistiques</h2>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">Monsters likés</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_likes']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Monsters notés</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_ratings']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Commentaires écrit</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_comments']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Réponses écrites</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_replies']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Monsters bus</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_drank']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Pages vues</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_views']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Likes sur commentaires</div>
                <div class="stat-value">' . htmlspecialchars($stats['total_comment_likes']) . '</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Easter-eggs débloqués</div>
                <div class="stat-value">' . count($eggs) . '</div>
            </div>
        </div>
    </div>

    <div class="section page-break">
        <h2 class="section-title">Monsters likés</h2>

        <div class="list-section">';

if (!empty($favorites)) {
    foreach ($favorites as $fav) {
        $html .= '
            <div class="item">
                <div class="item-title">' . htmlspecialchars($fav['nom'] ?? 'Sans nom') . '</div>
                <div class="item-subtitle">Note moyenne: ' . htmlspecialchars($fav['note']) . '/5</div>
            </div>';
    }
} else {
    $html .= '<div class="empty-section">Aucun monster liké</div>';
}

$html .= '
        </div>
    </div>';

if (!empty($comments)) {
    $html .= '
    <div class="section page-break">
        <h2 class="section-title">Commentaires</h2>

        <div class="list-section">';

    foreach ($comments as $comment) {
        $html .= '
            <div class="item">
                <div class="item-title">' . htmlspecialchars($comment['monster_name'] ?? 'Sans monster') . '</div>
                <div class="item-subtitle">Écrit le: ' . htmlspecialchars($comment['date'] ?? 'Date inconnue') . '</div>
                <div class="item-content">' . htmlspecialchars($comment['commentaire']) . '</div>
            </div>';
    }

    $html .= '
        </div>
    </div>';
}

if (!empty($eggs)) {
    $html .= '
    <div class="section page-break">
        <h2 class="section-title">Easter-eggs débloqués</h2>

        <div class="list-section">';

    foreach ($eggs as $egg) {
        $html .= '
            <div class="item">
                <div class="item-title">' . htmlspecialchars($egg['nom']) . '</div>
                <div class="item-content">' . htmlspecialchars($egg['description'] ?? '') . '</div>
            </div>';
    }

    $html .= '
        </div>
    </div>';
}

$html .= '

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