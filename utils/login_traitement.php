<?php
require_once __DIR__ . '/session.php'; 
require_once __DIR__ . '/database.php';

if (
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    !isset($_POST['captcha'])
) {
    header("Location: ../login?error=missing_fields");
    exit;
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);
$keepConnect = isset($_POST['keepConnected']) && $_POST['keepConnected'] == 'on';

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    header("Location: ../login?error=captcha_incorrect");
    exit;
}

try {
    $sql = "SELECT id_users, pseudo, mail, mdp, id_role
            FROM users
            WHERE mail = :mail";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'mail' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../login?error=no_account");
        exit;
    }

    if ($user['mdp'] == null) {
        header("Location: ../login?error=fake_account");
        exit;
    }

    if (!password_verify($password, $user['mdp'])) {
        header("Location: ../login?error=incorrect_password");
        exit;
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

    header("Location: ../?logged=true");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>