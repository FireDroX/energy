<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body>

    <header>
        <?php require_once '../components/navbar.php'; ?>
    </header>

    <div class="container">
        <main class="mt-5">

            <h1 class="mb-4">Inscription</h1>

            <form method="POST" action="../sessions/register_traitement.php">

                <div class="mb-3">
                    <label for="pseudo" class="form-label">Pseudo</label>
                    <input type="text" name="pseudo" class="form-control" id="pseudo" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        Confirmer le mot de passe
                    </label>
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary"> S'inscrire </button>

                <div class="mt-3">
                    <a href="../login/index.php"> Déjà un compte ? Se connecter </a>
                </div>

            </form>

        </main>
    </div>

    <footer>
    </footer>

</body>
</html>