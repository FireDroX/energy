<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../utils/database.php';
require_once __DIR__ . '/../../utils/session.php';
require_once __DIR__ . '/../../utils/loggers.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$currentUserId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT u.id_users, u.pseudo, r.role
    FROM users u
    INNER JOIN roles r ON r.id_role = u.id_role
    WHERE u.id_users = ?
');
$stmt->execute([$currentUserId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || strtolower($currentUser['role']) !== 'admin') {
    http_response_code(403);
    exit('Accès refusé');
}

$idMonster = (int)($_GET['id'] ?? 0);

if ($idMonster <= 0) {
    http_response_code(400);
    exit('ID Monster invalide');
}

$stmt = $pdo->prepare('
    SELECT id_monsters, nom, description, image
    FROM monsters
    WHERE id_monsters = ?
');
$stmt->execute([$idMonster]);
$monster = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$monster) {
    http_response_code(404);
    exit('Monster introuvable');
}

$stmt = $pdo->prepare('
    SELECT GROUP_CONCAT(t.nom ORDER BY t.nom SEPARATOR ", ") AS tags
    FROM monster_tags mt
    INNER JOIN tags t ON t.id_tags = mt.id_tags
    WHERE mt.id_monsters = ?
');
$stmt->execute([$idMonster]);
$tags = $stmt->fetchColumn() ?: 'Aucun tag';

$stmt = $pdo->prepare('
    SELECT
        (SELECT COUNT(*) FROM monster_favorites WHERE id_monsters = ?) AS favorites_count,
        (SELECT COUNT(*) FROM monster_drinks WHERE id_monsters = ?) AS drinks_count,
        (SELECT COUNT(*) FROM monster_views WHERE id_monsters = ?) AS views_count,
        (SELECT COUNT(*) FROM commentaires WHERE id_monsters = ?) AS comments_count,
        (SELECT COUNT(*) FROM notes WHERE id_monsters = ?) AS ratings_count,
        (SELECT ROUND(AVG(note), 2) FROM notes WHERE id_monsters = ?) AS average_rating,
        (SELECT COUNT(*) FROM likes l INNER JOIN commentaires c ON c.id_commentaires = l.id_commentaires WHERE c.id_monsters = ?) AS comment_likes_count,
        (SELECT MIN(date_view) FROM monster_views WHERE id_monsters = ?) AS first_view,
        (SELECT MAX(date_view) FROM monster_views WHERE id_monsters = ?) AS last_view
');
$stmt->execute([
    $idMonster,
    $idMonster,
    $idMonster,
    $idMonster,
    $idMonster,
    $idMonster,
    $idMonster,
    $idMonster,
    $idMonster
]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('
    SELECT ROUND(note) AS note_value, COUNT(*) AS total
    FROM notes
    WHERE id_monsters = ?
    GROUP BY ROUND(note)
    ORDER BY note_value DESC
');
$stmt->execute([$idMonster]);
$ratingRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
foreach ($ratingRows as $row) {
    $noteValue = (int)$row['note_value'];
    if ($noteValue >= 1 && $noteValue <= 5) {
        $ratingDistribution[$noteValue] = (int)$row['total'];
    }
}

$stmt = $pdo->prepare('
    SELECT c.commentaire, c.date, c.is_pinned, u.pseudo
    FROM commentaires c
    INNER JOIN users u ON u.id_users = c.id_users
    WHERE c.id_monsters = ?
    ORDER BY c.is_pinned DESC, c.date DESC
');
$stmt->execute([$idMonster]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function displayName($name): string
{
    return ucwords(str_replace('_', ' ', (string)$name));
}

function imageSource($image): string
{
    $image = (string)$image;

    if ($image === '') {
        return '';
    }

    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . '/' . ltrim($image, '/');
}

$monsterName = displayName($monster['nom']);
$imageSrc = imageSource($monster['image']);
$averageRating = $stats['average_rating'] !== null ? $stats['average_rating'] . ' / 5' : 'N/A';
$generatedAt = date('d/m/Y H:i');

$commentsHtml = '';

if (count($comments) === 0) {
    $commentsHtml = '<p class="muted">Aucun commentaire pour cette Monster.</p>';
} else {
    foreach ($comments as $comment) {
        $pinned = (int)$comment['is_pinned'] === 1 ? '<span class="badge">Épinglé</span>' : '';
        $commentsHtml .= '
            <div class="comment">
                <div class="comment-header">
                    <strong>' . e($comment['pseudo']) . '</strong>
                    <span>' . e($comment['date']) . '</span>
                    ' . $pinned . '
                </div>
                <p>' . nl2br(e($comment['commentaire'])) . '</p>
            </div>
        ';
    }
}

$ratingHtml = '';

for ($i = 5; $i >= 1; $i--) {
    $ratingHtml .= '
        <tr>
            <td>' . str_repeat('★', $i) . '</td>
            <td>' . $ratingDistribution[$i] . '</td>
        </tr>
    ';
}

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

        .monster-block {
            width: 100%;
            border-collapse: collapse;
        }

        .monster-img-cell {
            width: 180px;
            vertical-align: top;
        }

        .monster-img {
            width: 150px;
            max-height: 220px;
            object-fit: contain;
            border: 2px solid #111111;
            padding: 10px;
        }

        .info-table,
        .stats-table,
        .rating-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td,
        .stats-table th,
        .stats-table td,
        .rating-table th,
        .rating-table td {
            border: 1px solid #d0d0d0;
            padding: 8px;
            text-align: left;
        }

        .info-table th,
        .stats-table th,
        .rating-table th {
            background: #111111;
            color: #ffffff;
            width: 35%;
        }

        .stats-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-grid td {
            width: 33.33%;
            border: 1px solid #d0d0d0;
            padding: 12px;
            vertical-align: top;
        }

        .stat-label {
            display: block;
            color: #555555;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .stat-value {
            display: block;
            font-size: 18px;
            font-weight: bold;
        }

        .comment {
            border: 1px solid #d0d0d0;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .comment-header {
            color: #555555;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .comment-header strong {
            color: #111111;
            font-size: 12px;
        }

        .badge {
            background: #111111;
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 6px;
        }

        .muted {
            color: #777777;
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
            <h1 class="title">Rapport Monster</h1>
            <p class="subtitle">Export administrateur généré le ' . e($generatedAt) . '</p>
        </div>

        <div class="section">
            <h2 class="section-title">Informations générales</h2>
            <table class="monster-block">
                <tr>
                    <td class="monster-img-cell">' . ($imageSrc !== '' ? '<img src="' . e($imageSrc) . '" class="monster-img" alt="Monster">' : '<p class="muted">Aucune image</p>') . '</td>
                    <td>
                        <table class="info-table">
                            <tr><th>ID</th><td>' . e($monster['id_monsters']) . '</td></tr>
                            <tr><th>Nom</th><td>' . e($monsterName) . '</td></tr>
                            <tr><th>Nom BDD</th><td>' . e($monster['nom']) . '</td></tr>
                            <tr><th>Description</th><td>' . e($monster['description'] ?: 'Aucune description') . '</td></tr>
                            <tr><th>Tags</th><td>' . e($tags) . '</td></tr>
                            <tr><th>Image</th><td>' . e($monster['image'] ?: 'Aucune image') . '</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Statistiques principales</h2>
            <table class="stats-grid">
                <tr>
                    <td><span class="stat-label">Favoris</span><span class="stat-value">' . e($stats['favorites_count']) . '</span></td>
                    <td><span class="stat-label">Bue par des utilisateurs</span><span class="stat-value">' . e($stats['drinks_count']) . '</span></td>
                    <td><span class="stat-label">Vues</span><span class="stat-value">' . e($stats['views_count']) . '</span></td>
                </tr>
                <tr>
                    <td><span class="stat-label">Commentaires</span><span class="stat-value">' . e($stats['comments_count']) . '</span></td>
                    <td><span class="stat-label">Likes sur commentaires</span><span class="stat-value">' . e($stats['comment_likes_count']) . '</span></td>
                    <td><span class="stat-label">Note moyenne</span><span class="stat-value">' . e($averageRating) . '</span></td>
                </tr>
                <tr>
                    <td><span class="stat-label">Nombre de notes</span><span class="stat-value">' . e($stats['ratings_count']) . '</span></td>
                    <td><span class="stat-label">Première vue</span><span class="stat-value">' . e($stats['first_view'] ?: 'N/A') . '</span></td>
                    <td><span class="stat-label">Dernière vue</span><span class="stat-value">' . e($stats['last_view'] ?: 'N/A') . '</span></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Répartition des notes</h2>
            <table class="rating-table">
                <tr><th>Note</th><th>Nombre</th></tr>
                ' . $ratingHtml . '
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Commentaires</h2>
            ' . $commentsHtml . '
        </div>

        <div class="footer">
            Export généré par ' . e($currentUser['pseudo']) . ' depuis le panel administrateur Monster Energy Carousel.
        </div>
    </div>
</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

addLog(
    $pdo,
    $currentUserId,
    'EXPORT_MONSTER_PDF',
    'Export PDF administrateur de la Monster ' . $monster['nom'] . ' (#' . $idMonster . ')'
);

$filename = 'rapport_monster_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $monster['nom']) . '.pdf';

$dompdf->stream($filename, [
    'Attachment' => true
]);

exit;
