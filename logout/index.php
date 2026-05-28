<?php
require_once __DIR__ . '/../utils/session.php'; 

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
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">

                <div class="card shadow p-4 text-center">
                    <h1 class="mb-3">Déconnexion réussie</h1>

                    <p class="mb-4">
                        Vous avez bien été déconnecté.
                    </p>

                    <a href="/" class="btn btn-dark">
                        Retour à l'accueil
                    </a>
                </div>

            </div>
        </div>
    </div>

</body>
</html>