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
    header("Location: ../../register?warning=missing_fields");
    exit;
}

$pseudo = trim($_POST['pseudo']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPassword = trim($_POST['confirm_password']);

if ($password !== $confirmPassword) {
    header("Location: ../../register?warning=password_mismatch");
    exit;
}

$captcha = trim($_POST['captcha']);
$captcha_answer = $_SESSION['captcha_answer'];

if (!in_array(strtoLower($captcha), array_map('strtoLower', $captcha_answer))) {
    header("Location: ../../register?warning=captcha_incorrect");
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
        header("Location: ../../register?warning=email_exists");
        exit;
    }

    $mailer = new Mailer();
    
    $tempUuid = bin2hex(random_bytes(16));
    $result = $mailer->sendWelcome($email, $pseudo, $tempUuid);
    
    if (!$result) {
        throw new Exception('Erreur lors de l\'envoi du mail de confirmation');
    }

    $sql = "INSERT INTO users (pseudo, mail, mdp, id_role, uuid)
            VALUES (:pseudo, :mail, :mdp, :id_role, :uuid)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'pseudo' => $pseudo,
        'mail' => $email,
        'mdp' => $passwordHash,
        'id_role' => 5,
        'uuid' => $tempUuid
    ]);

    $id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_users = ?");
    $stmt->execute([$id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    addLog(
        $pdo,
        $user['id_users'],
        'REGISTER',
        'Création du compte ' . $user['pseudo']
    );

    $stmt = $pdo->prepare("
        INSERT INTO messages (contenu, sender_id, receiver_id) VALUES 
        (:msg1, 1, :id),
        (:msg2, 1, :id),
        (:msg3, 1, :id);
    ");
    $stmt->execute([
        ':msg1' => 'Bienvenue ' . htmlspecialchars($pseudo) . ' !',
        ':msg2' => 'Explore les collections, participe aux classements et découvrez des saveurs uniques.',
        ':msg3' => 'Active la newsletter dans les paramètres de ton compte pour recevoir les nouveautés directement par mail.',
        ':id' => $user['id_users']
    ]);

    header("Location: ../../login/?success=mail_sent");
    exit;
} catch (Exception $e) {
    header("Location: ../../register/?error=mail_send_failed");
    exit;
} catch (PDOException $e) {
    header("Location: ../../register/?error=database_error");
    exit;
}

?>