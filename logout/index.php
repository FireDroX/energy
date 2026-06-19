<?php
require_once __DIR__ . '/../utils/session.php';

if (isset($_COOKIE['remember_token'])) {

    $stmt = $pdo->prepare("
        DELETE FROM remember_tokens
        WHERE token_hash = :token_hash
    ");

    $stmt->execute([
        'token_hash' => hash(
            'sha256',
            $_COOKIE['remember_token']
        )
    ]);

    setcookie(
        'remember_token',
        '',
        time() - 3600,
        '/'
    );
}

$_SESSION = [];
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/styles/home.css">
    <link rel="stylesheet" href="/panel/captcha/styles.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <main class="container card shadow p-4 text-center">
                    <h1 class="mb-3">Déconnexion réussie</h1>
                    <p class="mb-4 text-light">
                        Vous avez bien été déconnecté.
                    </p>
                    <a href="/" class="btn btn-primary">
                        Retour à l'accueil
                    </a>
                </main>
            </div>
        </div>
    </div>
</body>
</html>