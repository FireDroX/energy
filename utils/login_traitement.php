<?php
require_once __DIR__ . '/session.php'; 
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/loggers.php';

if (
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    !isset($_POST['captcha'])
) {
    header("Location: ../login?warning=missing_fields");
    exit;
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);
$keepConnect = isset($_POST['keepConnected']) && $_POST['keepConnected'] == 'on';

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    header("Location: ../login?warning=captcha_incorrect");
    exit;
}

try {
    $sql = "SELECT id_users, pseudo, mail, mdp, id_role, deactivated
            FROM users
            WHERE mail = :mail";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'mail' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../login?warning=no_account");
        exit;
    }

    if ($user['mdp'] == null) {
        header("Location: ../login?warning=deactivated_account");
        exit;
    }

    if (!password_verify($password, $user['mdp'])) {
        header("Location: ../login?warning=incorrect_password");
        exit;
    }

    if(!is_null($user['deactivated'])) {
        header("Location: ../login?warning=deactivated_account");
        exit;
    }

    $_SESSION['user'] = [
        'id' => $user['id_users'],
        'pseudo' => $user['pseudo'],
        'email' => $user['mail'],
        'role' => $user['id_role'],
        'is_active' => is_null($user['deactivated']),
        'lastUpdate' => time()
    ];

    if ($keepConnect) {

        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("
            INSERT INTO remember_tokens (
                user_id,
                token_hash,
                expires_at
            )
            VALUES (
                :user_id,
                :token_hash,
                DATE_ADD(NOW(), INTERVAL 30 DAY)
            )
        ");

        $stmt->execute([
            'user_id' => $user['id_users'],
            'token_hash' => hash('sha256', $token)
        ]);
    }

    addLog(
        $pdo,
        $user['id_users'],
        'LOGIN',
        'Connexion réussie'
    );

    header("Location: ../?info=logged");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>