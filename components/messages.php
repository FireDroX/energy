<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/conversations.php';

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_active']) {
    return;
}

$userId = (int) $_SESSION['user']['id'];
$conversations = getConversationsForUser($pdo, $userId);

$totalNonLus = 0;
foreach ($conversations as $conv) {
    $totalNonLus += $conv['non_lus'];
}

$stmtUsers = $pdo->prepare("
    SELECT id_users, pseudo FROM users
    WHERE id_users != :user_id AND deactivated IS NULL
    ORDER BY pseudo ASC
");
$stmtUsers->execute(['user_id' => $userId]);
$allUsers = $stmtUsers->fetchAll();
?>

<div id="msg-widget-root">
    <button id="msg-bubble" type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="msg-modal" aria-label="Ouvrir les messages">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 4h16v12H7l-3 3V4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round" fill="none"/>
        </svg>
        <span id="msg-badge" class="<?= $totalNonLus > 0 ? 'is-visible' : '' ?>">
            <?= $totalNonLus > 9 ? '9+' : $totalNonLus ?>
        </span>
    </button>

    <div id="msg-overlay"></div>

    <div id="msg-modal" role="dialog" aria-modal="true" aria-labelledby="msg-modal-title">
        <div id="msg-modal-header">
            <h2 id="msg-modal-title">Messages</h2>
            <div id="msg-modal-actions">
                <button id="msg-new-toggle" type="button" aria-label="Nouveau message" title="Nouveau message">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
                <button id="msg-close" type="button" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="msg-list">
            <?php if (empty($conversations)): ?>
                <div id="msg-empty">
                    <p>Aucune conversation pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <?php
                        $isUnread = $conv['non_lus'] > 0;
                        $isFromThem = (int) $conv['sender_id'] !== $userId;
                        $preview = shortenText($conv['dernier_message']);
                    ?>
                    <a class="msg-item <?= $isUnread ? 'is-unread' : '' ?>"
                        href="/conversation?with=<?= (int) $conv['id_users'] ?>"
                        data-sender-id="<?= (int) $conv['id_users'] ?>">
                        <span class="msg-item-avatar"><?= mb_strtoupper(mb_substr($conv['pseudo'], 0, 1)) ?></span>
                        <span class="msg-item-body">
                            <span class="msg-item-top">
                                <span class="msg-item-name"><?= htmlspecialchars($conv['pseudo']) ?></span>
                                <span class="msg-item-time" data-date="<?= htmlspecialchars($conv['derniere_date']) ?>"></span>
                            </span>
                            <span class="msg-item-preview">
                                <?php if (!$isFromThem): ?><span class="msg-item-you">Vous : </span><?php endif; ?>
                                <?= htmlspecialchars($preview) ?>
                            </span>
                        </span>
                        <?php if ($isUnread): ?>
                            <span class="msg-item-dot"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form id="msg-new-form">
            <div id="msg-new-body">
                <label for="msg-new-recipient">Destinataire</label>
                <select id="msg-new-recipient" required>
                    <option value="" disabled selected>Choisir une personne…</option>
                    <?php foreach ($allUsers as $u): ?>
                        <option value="<?= (int) $u['id_users'] ?>"><?= htmlspecialchars($u['pseudo']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="msg-new-content">Message</label>
                <textarea id="msg-new-content" rows="4" maxlength="255" placeholder="Écris ton message…" required></textarea>
                <div id="msg-new-char-count">0 / 255</div>

                <p id="msg-new-error"></p>
            </div>

            <div id="msg-new-footer">
                <button type="button" id="msg-new-cancel">Annuler</button>
                <button type="submit" id="msg-new-submit">Envoyer</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="/styles/messages.css">
<script src="/utils/messages_widget.js" defer></script>