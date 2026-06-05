<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/database.php';

if (
    !isset($_POST['pseudo']) ||
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    !isset($_POST['confirm_password']) ||
    !isset($_POST['captcha'])
) {
    header("Location: ../register?error=missing_fields");
    exit;
}

$pseudo = trim($_POST['pseudo']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPassword = trim($_POST['confirm_password']);

if ($password !== $confirmPassword) {
    header("Location: ../register?error=password_mismatch");
    exit;
}

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    header("Location: ../register?error=captcha_incorrect");
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
        header("Location: ../register?error=email_exists");
        exit;
    }

    $sql = "INSERT INTO users (pseudo, mail, mdp, id_role)
            VALUES (:pseudo, :mail, :mdp, :id_role)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'pseudo' => $pseudo,
        'mail' => $email,
        'mdp' => $passwordHash,
        'id_role' => 2
    ]);

    header("Location: ../login/?registered=true");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>