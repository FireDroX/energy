<?php
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

$userId = (int) $_SESSION['user']['id'];
$otherId = isset($_GET['with']) ? (int) $_GET['with'] : 0;

if ($otherId <= 0 || $otherId === $userId) {
    header('Location: /');
    exit;
}

$stmtUser = $pdo->prepare('SELECT id_users, pseudo FROM users WHERE id_users = :id');
$stmtUser->execute(['id' => $otherId]);
$otherUser = $stmtUser->fetch();

if (!$otherUser) {
    header('Location: /');
    exit;
}

$stmtMsgs = $pdo->prepare("
    SELECT id_messages, contenu, date_envoie, sender_id, receiver_id
    FROM messages
    WHERE (sender_id = :user_id AND receiver_id = :other_id)
       OR (sender_id = :other_id AND receiver_id = :user_id)
    ORDER BY id_messages ASC
");
$stmtMsgs->execute(['user_id' => $userId, 'other_id' => $otherId]);
$messages = $stmtMsgs->fetchAll();

$markRead = $pdo->prepare("
    UPDATE messages SET lu = 1
    WHERE receiver_id = :user_id AND sender_id = :other_id AND lu = 0
");
$markRead->execute(['user_id' => $userId, 'other_id' => $otherId]);

$lastMessageId = 0;
if (count($messages) > 0) {
    $lastMessage = end($messages);
    $lastMessageId = (int) $lastMessage['id_messages'];
}

function formatGroupDate(string $dateText): string
{
    $date = new DateTime($dateText);
    $today = new DateTime('today');
    $yesterday = new DateTime('yesterday');

    if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
        return 'Aujourd’hui';
    }
    if ($date->format('Y-m-d') === $yesterday->format('Y-m-d')) {
        return 'Hier';
    }

    $jours = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
    $mois = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

    return $jours[(int) $date->format('w')] . ' ' . (int) $date->format('j') . ' ' . $mois[(int) $date->format('n')];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster | <?= htmlspecialchars($otherUser['pseudo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="/styles/home.css">
</head>
<body>
    <header><?php require_once '../components/navbar.php'; ?></header>
    <?php require_once '../components/alert.php'; ?>

    <main class="container mt-4" data-other-id="<?= (int) $otherId ?>" data-last-id="<?= $lastMessageId ?>">
        <header id="conv-header">
            <a id="conv-back" href="/" aria-label="Retour">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <div id="conv-avatar"><?= mb_strtoupper(mb_substr($otherUser['pseudo'], 0, 1)) ?></div>
            <div>
                <p id="conv-name"><?= htmlspecialchars($otherUser['pseudo']) ?></p>
                <p id="conv-status">Conversation</p>
            </div>
        </header>

        <div id="conv-thread">
            <?php if (empty($messages)): ?>
                <p id="conv-empty">Aucun message. Écris le premier !</p>
            <?php else: ?>
                <?php $currentDay = null; ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                        $isMine = (int) $msg['sender_id'] === $userId;
                        $msgDay = (new DateTime($msg['date_envoie']))->format('Y-m-d');
                    ?>
                    <?php if ($msgDay !== $currentDay): ?>
                        <?php $currentDay = $msgDay; ?>
                        <div class="conv-day-divider"><?= formatGroupDate($msg['date_envoie']) ?></div>
                    <?php endif; ?>
                    <div class="conv-row <?= $isMine ? 'is-mine' : 'is-theirs' ?>" data-id="<?= (int) $msg['id_messages'] ?>">
                        <div class="conv-bubble-wrap">
                            <div class="conv-bubble"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                            <div class="conv-time"><?= (new DateTime($msg['date_envoie']))->format('H:i') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form id="conv-composer" autocomplete="off">
            <textarea id="conv-input" rows="1" placeholder="Écris un message…" maxlength="255" required></textarea>
            <button id="conv-send" type="submit" aria-label="Envoyer">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 11l18-8-8 18-2-8-8-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" fill="none"/>
                </svg>
            </button>
        </form>
        <div id="conv-char-count">0 / 255</div>
    </main>

    <script>
        const currentUserId = <?= $userId ?>;
    </script>
    <script src="/utils/messages/conversation.js" defer></script>

    <?php require_once __DIR__ . '/../components/messages.php'; ?>
</body>
</html>