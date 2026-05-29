<?php
require_once __DIR__ . '/../utils/session.php'; 
require_once __DIR__ . '/../utils/functions.php';

if (
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    !isset($_POST['captcha'])
) {
    die("Veuillez remplir tous les champs.");
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);
$keepConnect = isset($_POST['keepConnected']) && $_POST['keepConnected'] == 'on';

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    die("Captcha incorrect.");
}

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};port={$_ENV['DB_PORT']};charset=utf8",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD']
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id_users, pseudo, mail, mdp, id_role
            FROM users
            WHERE mail = :mail";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'mail' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Aucun compte trouvé avec cet email.");
    }

    if ($user['mdp'] == null) {
        die("Faux compte.");
    }

    if (!password_verify($password, $user['mdp'])) {
        die("Mot de passe incorrect.");
    }

    $_SESSION['user'] = [
        'id' => $user['id_users'],
        'pseudo' => $user['pseudo'],
        'email' => $user['mail'],
        'role' => $user['id_role'],
        'lastUpdate' => time()
    ];

    if ($keepConnect) {
        setcookie(
            'user_email',
            $user['mail'],
            time() + (86400 * 30),
            "/"
        );
    }

    header("Location: ../");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>