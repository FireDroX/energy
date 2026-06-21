<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../mailer.php';
require_once __DIR__ . '/../loggers.php';

if (
    !isset($_POST['pseudo']) ||
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    !isset($_POST['confirm_password']) ||
    !isset($_POST['captcha'])
) {
    header("Location: ../register?warning=missing_fields");
    exit;
}

$pseudo = trim($_POST['pseudo']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPassword = trim($_POST['confirm_password']);

if ($password !== $confirmPassword) {
    header("Location: ../register?warning=password_mismatch");
    exit;
}

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    header("Location: ../register?warning=captcha_incorrect");
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $checkSql = "SELECT mail FROM users WHERE mail = :mail";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
        'mail' => $email
    ]);

    if ($checkStmt->fetch()) {
        header("Location: ../register?warning=email_exists");
        exit;
    }

    $sql = "INSERT INTO users (pseudo, mail, mdp, id_role, uuid)
            VALUES (:pseudo, :mail, :mdp, :id_role, UUID())";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'pseudo' => $pseudo,
        'mail' => $email,
        'mdp' => $passwordHash,
        'id_role' => 5
    ]);

    $id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_users = ?");
    $stmt->execute([$id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $mailer = new Mailer();
    $result = $mailer->sendWelcome($user['mail'], $user['pseudo'], $user['uuid']);

    addLog(
        $pdo,
        $user['id_users'],
        'REGISTER',
        'Création du compte ' . $user['pseudo']
    );
  
    header("Location: ../login/?success=mail_sent");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

?>