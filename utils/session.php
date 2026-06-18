<?php
require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (
    !isset($_SESSION['user'])
    && isset($_COOKIE['remember_token'])
) {

    $tokenHash = hash(
        'sha256',
        $_COOKIE['remember_token']
    );

    $stmt = $pdo->prepare("
        SELECT
            u.id_users,
            u.pseudo,
            u.mail,
            u.id_role,
            u.deactivated
        FROM remember_tokens rt
        JOIN users u
            ON rt.user_id = u.id_users
        WHERE rt.token_hash = :token_hash
        AND rt.expires_at > NOW()
        LIMIT 1
    ");

    $stmt->execute([
        'token_hash' => $tokenHash
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && is_null($user['deactivated'])) {

        $_SESSION['user'] = [
            'id' => $user['id_users'],
            'pseudo' => $user['pseudo'],
            'email' => $user['mail'],
            'role' => $user['id_role'],
            'is_active' => true,
            'lastUpdate' => time()
        ];
    }
}

$maxIdleTime = 60 * 5;

if (
    isset($_SESSION['user']['lastUpdate'])
    && time() - $_SESSION['user']['lastUpdate'] > $maxIdleTime
) {
    session_unset();
    session_destroy();

    header('Location: /login?warning=session_expired');
    exit;
}

if (isset($_SESSION['user'])) {
    $_SESSION['user']['lastUpdate'] = time();
}