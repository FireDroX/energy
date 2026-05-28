<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /login");
    exit;
}

$user = $_SESSION['user'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster | Mon Compte</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">

        <div class="row justify-content-center">
            <div class="col-md-7">

                <div class="card shadow p-4">
                    <h1 class="mb-4 text-center">Mon Compte</h1>

                    <div class="mb-3">
                        <strong>ID :</strong>
                        <?= htmlspecialchars($user['id']) ?>
                    </div>

                    <div class="mb-3">
                        <strong>Pseudo :</strong>
                        <?= htmlspecialchars($user['pseudo']) ?>
                    </div>

                    <div class="mb-3">
                        <strong>Email :</strong>
                        <?= htmlspecialchars($user['email']) ?>
                    </div>

                    <div class="mb-4">
                        <strong>Rôle :</strong>
                        <?= htmlspecialchars($user['role']) ?>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="/" class="btn btn-dark">
                            Accueil
                        </a>

                        <a href="/logout" class="btn btn-outline-danger">
                            Déconnexion
                        </a>
                    </div>

                </div>

            </div>
        </div>

    </div>

</body>
</html>
