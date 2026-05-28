<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../utils/functions.php';


function generateCaptcha() {
	try {
		$pdo = new PDO(
			"mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']};charset=utf8",
			$_ENV['DB_USER'],
			$_ENV['DB_PASSWORD']
		);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->query("SELECT * FROM captcha ORDER BY RAND() LIMIT 1");

		$captcha = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		$captcha = ['id_captcha' => 0, 'question' => 'Quels sont les 2 délégués de la classe ?', 'reponse' => '["hassrol","alex"]'];
	}

	$_SESSION['captcha_answer'] = json_decode($captcha['reponse'], true);
	return $captcha;
}

$captcha = generateCaptcha();
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

		<div class=container>
			<main class="mt-5">
				<h1 class="mb-4">Connexion</h1>
				<form method="POST" action="../sessions/login_traitement.php">
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
							<a href="../register/index.php">Pas encore de compte ? S'inscrire</a>
					</div>
				</form>
			</main>
		</div>
	</body>
</html>