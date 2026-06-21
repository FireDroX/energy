<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/database.php';

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_active']) {
    return;
}

$userId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT
      u.id_users,
      u.pseudo,
      m.contenu AS dernier_message,
      m.date_envoie AS derniere_date,
      m.sender_id,
      (
        SELECT COUNT(*) FROM messages
        WHERE receiver_id = :user_id AND sender_id = u.id_users AND lu = 0
      ) AS non_lus
    FROM messages m
    INNER JOIN users u ON u.id_users = IF(m.sender_id = :user_id, m.receiver_id, m.sender_id)
    WHERE m.id_messages = (
      SELECT MAX(id_messages) FROM messages
      WHERE (sender_id = :user_id AND receiver_id = u.id_users)
        OR (sender_id = u.id_users AND receiver_id = :user_id)
    )
    GROUP BY u.id_users
    ORDER BY derniere_date DESC
");

$stmt->execute([
    'user_id' => $userId,
]);

$conversations = $stmt->fetchAll();

$totalNonLus = array_sum(array_column($conversations, 'non_lus'));
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
            <button id="msg-close" type="button" aria-label="Fermer">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
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
                        $preview = mb_strlen($conv['dernier_message']) > 60
                            ? mb_substr($conv['dernier_message'], 0, 60) . '…'
                            : $conv['dernier_message'];
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
                                <?= !$isFromThem ? '<span class="msg-item-you">Vous : </span>' : '' ?><?= htmlspecialchars($preview) ?>
                            </span>
                        </span>
                        <?php if ($isUnread): ?>
                            <span class="msg-item-dot"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/styles/messages.css">

<script defer>
    const root = document.getElementById('msg-widget-root');
    const bubble = document.getElementById('msg-bubble');
    const overlay = document.getElementById('msg-overlay');
    const modal = document.getElementById('msg-modal');
    const closeBtn = document.getElementById('msg-close');
    const badge = document.getElementById('msg-badge');

    function openModal() {
        root.classList.add('is-open');
        bubble.setAttribute('aria-expanded', 'true');
        document.addEventListener('keydown', onKeydown);
    }

    function closeModal() {
        root.classList.remove('is-open');
        bubble.setAttribute('aria-expanded', 'false');
        document.removeEventListener('keydown', onKeydown);
    }

    function onKeydown(e) {
        if (e.key === 'Escape') closeModal();
    }

    bubble.addEventListener('click', () => {
        root.classList.contains('is-open') ? closeModal() : openModal();
    });
    overlay.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    document.querySelectorAll('.msg-item').forEach((item) => {
        item.addEventListener('click', function (e) {
            const senderId = this.dataset.senderId;
            fetch('../api/messages/mark_as_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ sender_id: senderId }),
            });
        });
    });

    function formatRelativeTime(dateStr) {
        const date = new Date(dateStr.replace(' ', 'T'));
        const diffSec = Math.round((Date.now() - date.getTime()) / 1000);

        if (diffSec < 60) return 'à l’instant';
        if (diffSec < 3600) return `il y a ${Math.floor(diffSec / 60)} min`;
        if (diffSec < 86400) return `il y a ${Math.floor(diffSec / 3600)} h`;
        if (diffSec < 172800) return 'hier';
        return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
    }

    document.querySelectorAll('.msg-item-time').forEach((el) => {
        el.textContent = formatRelativeTime(el.dataset.date);
    });

    window.msgWidget = {
        updateBadge(count) {
            badge.textContent = count > 9 ? '9+' : count;
            if (count > 0) {
                badge.classList.add('is-visible');
                badge.classList.remove('is-pulsing');

                requestAnimationFrame(() => badge.classList.add('is-pulsing'));
            } else {
                badge.classList.remove('is-visible');
            }
        },
        open: openModal,
        close: closeModal,
    };
</script>