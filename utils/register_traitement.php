<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/loggers.php';

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

    $sql = "INSERT INTO users (pseudo, mail, mdp, id_role)
            VALUES (:pseudo, :mail, :mdp, :id_role)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'pseudo' => $pseudo,
        'mail' => $email,
        'mdp' => $passwordHash,
        'id_role' => 2
    ]);

    addLog(
        $pdo,
        $newUserId,
        'REGISTER',
        'Création du compte ' . $pseudo
    );

    header("Location: ../login/?success=registered");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

?>