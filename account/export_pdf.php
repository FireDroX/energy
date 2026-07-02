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

$html = "
<h1>Informations personnelles</h1>

<table border='1' cellpadding='8' cellspacing='0' width='100%'>
<tr>
    <th>ID</th>
    <td>{$user['id_users']}</td>
</tr>

<tr>
    <th>Pseudo</th>
    <td>{$user['pseudo']}</td>
</tr>

<tr>
    <th>Email</th>
    <td>{$user['mail']}</td>
</tr>

<tr>
    <th>Rôle</th>
    <td>{$user['role']}</td>
</tr>

<tr>
    <th>Date d'inscription</th>
    <td>{$user['created']}</td>
</tr>

<tr>
    <th>Newsletter</th>
    <td>" . ($user['newsletter'] ? 'Oui' : 'Non') . "</td>
</tr>
</table>
";

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