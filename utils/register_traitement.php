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
    echo "<script>
        alert('Veuillez remplir tous les champs.');
        window.location.href = '/register';
    </script>" ;
    exit;
}

$pseudo = trim($_POST['pseudo']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPassword = trim($_POST['confirm_password']);

if ($password !== $confirmPassword) {
    echo "<script>
        alert('Les mots de passe ne correspondent pas.');
        window.location.href = '/register';
    </script>" ;
    exit;
}

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    echo "<script>
        alert('Captcha incorrect.');
        window.location.href = '/register';
    </script>" ;
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
        echo "<script>
            alert('Cet utilisateur existe déjà.');
            window.location.href = '/register';
        </script>" ;
        exit;
    }

    $checkPseudoSql = "SELECT pseudo FROM users WHERE pseudo = :pseudo";
    $checkPseudoStmt = $pdo->prepare($checkPseudoSql);
    $checkPseudoStmt->execute([
        'pseudo' => $pseudo
    ]);

    if ($checkPseudoStmt->fetch()) {
        echo "<script>
            alert('Ce pseudo est déjà utilisé.');
            window.location.href = '/register';
        </script>" ;
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

    header("Location: ../login/");
    exit;

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>