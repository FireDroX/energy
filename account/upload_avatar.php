<?php

require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

if (
    !isset($_FILES['avatar']) ||
    $_FILES['avatar']['error'] !== UPLOAD_ERR_OK
) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT avatar
    FROM users
    WHERE id_users = ?
");
$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit;
}

if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
    die("L'image dépasse 2 Mo.");
}

$extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

$extensionsAutorisees = [
    'jpg',
    'jpeg',
    'png',
    'webp'
];

if (!in_array($extension, $extensionsAutorisees)) {
    die("Format d'image non autorisé.");
}

switch ($extension) {

    case 'jpg':
    case 'jpeg':
        $source = imagecreatefromjpeg($_FILES['avatar']['tmp_name']);
        break;

    case 'png':
        $source = imagecreatefrompng($_FILES['avatar']['tmp_name']);
        break;

    case 'webp':
        $source = imagecreatefromwebp($_FILES['avatar']['tmp_name']);
        break;

    default:
        die("Format invalide.");
}

if (!$source) {
    die("Impossible de lire l'image.");
}

$uploadDir = __DIR__ . '/../uploads/avatars/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = uniqid('avatar_', true) . '.webp';

$taille = 256;

$largeur = imagesx($source);
$hauteur = imagesy($source);

if ($largeur > $hauteur) {

    $crop = $hauteur;

    $srcX = intval(($largeur - $hauteur) / 2);
    $srcY = 0;

} else {

    $crop = $largeur;

    $srcX = 0;
    $srcY = intval(($hauteur - $largeur) / 2);
}

$destination = imagecreatetruecolor($taille, $taille);

imagealphablending($destination, false);
imagesavealpha($destination, true);

imagecopyresampled(
    $destination,
    $source,
    0,
    0,
    $srcX,
    $srcY,
    $taille,
    $taille,
    $crop,
    $crop
);

imagewebp(
    $destination,
    $uploadDir . $fileName,
    85
);

imagedestroy($source);
imagedestroy($destination);

if (
    !empty($user['avatar']) &&
    file_exists($uploadDir . $user['avatar'])
) {
    unlink($uploadDir . $user['avatar']);
}

$stmt = $pdo->prepare("
    UPDATE users
    SET avatar = ?
    WHERE id_users = ?
");

$stmt->execute([
    $fileName,
    $userId
]);

$_SESSION['user']['avatar'] = $fileName;

addLog(
    $pdo,
    $userId,
    'MODIFICATION_AVATAR',
    'Modification de la photo de profil'
);

header('Location: index.php');
exit;
?>