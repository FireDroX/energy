<?php 
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/functions.php';
require_once __DIR__ . '/../utils/database.php';

require_once __DIR__ . '/../components/alert.php';

$captcha = generateCaptcha($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Monster | Connexion</title>

		<link rel="shortcut icon" href="/favicon.png" type="image/png">

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	</head>
	<body>
		<header>
			<?php require_once '../components/navbar.php'; ?>
		</header>

		<?php if (isset($_GET['error'])) { 
			switch($_GET['error']) {
				case 'missing_fields':
					echo createAlert("Veuillez remplir tous les champs !", "danger");
					break;
				case 'captcha_incorrect':
					echo createAlert("Captcha incorrect !", "danger");
					break;
				case 'no_account':
					echo createAlert("Aucun compte trouvé avec cet email !", "danger");
					break;
				case 'fake_account':
					echo createAlert("Cet email est un faux compte de test !", "danger");
					break;
				case 'incorrect_password':
					echo createAlert("Mot de passe incorrect !", "danger");
					break;
				case 'deactivated_account':
					echo createAlert("Ce compte est désactivé !", "danger");
					break;
			}
    } ?>

		<?php if (isset($_GET['registered']) && $_GET['registered'] == 'true') { 
			echo createAlert("Compte créé avec succès !", "success");
		} ?>

		<div class=container>
			<main class="mt-5">
				<h1 class="mb-4">Connexion</h1>
				<form method="POST" action="../utils/login_traitement.php">
					<div class="mb-3">
						<label for="email" class="form-label">Adresse email</label>
						<input type="email" name="email" class="form-control" id="email" required>
					</div>

					<div class="mb-3">
						<label for="password" class="form-label">Mot de passe</label>
						<input type="password" name="password" class="form-control" id="password" required>
					</div>

					<div class="mb-3">
						<label for="captcha" class="form-label">Captcha: <?= htmlspecialchars($captcha['question']) ?></label>
						<input type="text" name="captcha" class="form-control" id="captcha" required>
					</div>

					<div class="mb-3 form-check">
						<input type="checkbox" name="keepConnected" class="form-check-input" id="keepConnected">
						<label class="form-check-label" for="keepConnected">Préserver ma connexion</label>
					</div>

					<button type="submit" class="btn btn-primary">Se connecter</button>

					<div class="mt-3">
							<a href="../register/">Pas encore de compte ? S'inscrire</a>
					</div>
				</form>
			</main>
		</div>
	</body>
</html>