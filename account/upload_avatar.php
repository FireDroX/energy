<?php

require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

if (!isset($_FILES['avatar']) ||
    $_FILES['avatar']['error'] !== UPLOAD_ERR_OK
) {
    header('Location: index.php?warning=avatar_no_file');
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
    header('Location: index.php?warning=avatar_oversized');
    exit;
}

$extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

$extensionsAutorisees = [
    'jpg',
    'jpeg',
    'png',
    'webp',
    'gif'
];

if (!in_array($extension, $extensionsAutorisees)) {
    header('Location: index.php?warning=avatar_invalid_format');
    exit;
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

    case 'gif':
        $source = imagecreatefromgif($_FILES['avatar']['tmp_name']);
        break;

    default:
        header('Location: index.php?warning=avatar_invalid_format');
        exit;
}

if (!$source) {
    header('Location: index.php?warning=avatar_unreadable');
    exit;
}

$uploadDir = __DIR__ . '/../uploads/avatars/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Preserve GIF format for animated GIFs
$fileName = uniqid('avatar_', true) . ($extension === 'gif' ? '.gif' : '.webp');

// For GIFs, skip resizing to preserve animation
if ($extension === 'gif') {
    // Copy GIF directly with minimal processing
    $newPath = $uploadDir . $fileName;
    copy($_FILES['avatar']['tmp_name'], $newPath);
} else {
    // For other formats, resize and convert to WebP
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

    imagedestroy($destination);
}

imagedestroy($source);

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