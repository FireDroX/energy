<?php
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/session.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../utils/loggers.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);

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

$stmtMsgs = $pdo->prepare(
    'SELECT id_messages, contenu, date_envoie, sender_id, receiver_id, lu
      FROM messages
      WHERE (sender_id = :user_id AND receiver_id = :other_id)
        OR (sender_id = :other_id AND receiver_id = :user_id)
      ORDER BY id_messages ASC'
);
$stmtMsgs->execute([
    'user_id' => $userId,
    'other_id' => $otherId,
]);
$messages = $stmtMsgs->fetchAll();

$update = $pdo->prepare(
    'UPDATE messages SET lu = 1 WHERE receiver_id = :user_id AND sender_id = :other_id AND lu = 0'
);

$update->execute(['user_id' => $userId, 'other_id' => $otherId]);

$lastMessageId = !empty($messages) ? (int) end($messages)['id_messages'] : 0;

function formatGroupDate(string $dateStr): string {
    $date = new DateTime($dateStr);
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
    <title>Conversation avec <?= htmlspecialchars($otherUser['pseudo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="/styles/home.css">
    <link rel="stylesheet" href="/panel/captcha/styles.css">
  </head>
  <body>
    <header><?php require_once '../components/navbar.php'; ?></header>
    <?php require_once '../components/alert.php' ?>

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
              <?php
              $currentDay = null;
              foreach ($messages as $msg):
                  $isMine = (int) $msg['sender_id'] === $userId;
                  $msgDay = (new DateTime($msg['date_envoie']))->format('Y-m-d');
                  if ($msgDay !== $currentDay):
                      $currentDay = $msgDay;
              ?>
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

  <script defer>
    const page = document.querySelector('.container');
    const otherId = page.dataset.otherId;
    let lastId = parseInt(page.dataset.lastId, 10) || 0;

    const thread = document.getElementById('conv-thread');
    const empty = document.getElementById('conv-empty');
    const form = document.getElementById('conv-composer');
    const input = document.getElementById('conv-input');
    const sendBtn = document.getElementById('conv-send');
    const charCount = document.getElementById('conv-char-count');

    const dayLabels = {
        get: (dateObj) => {
            const today = new Date(); today.setHours(0,0,0,0);
            const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
            const d = new Date(dateObj); d.setHours(0,0,0,0);

            if (d.getTime() === today.getTime()) return 'Aujourd’hui';
            if (d.getTime() === yesterday.getTime()) return 'Hier';
            return dateObj.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
        }
    };

    function scrollToBottom() {
        thread.scrollTop = thread.scrollHeight;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function getLastDayDivider() {
        const dividers = thread.querySelectorAll('.conv-day-divider');
        return dividers.length ? dividers[dividers.length - 1].textContent : null;
    }

    function appendMessage(msg, isMine) {
        if (empty) empty.remove();

        const date = new Date(msg.date_envoie.replace(' ', 'T'));
        const dayLabel = dayLabels.get(date);

        if (getLastDayDivider() !== dayLabel) {
            const divider = document.createElement('div');
            divider.className = 'conv-day-divider';
            divider.textContent = dayLabel;
            thread.appendChild(divider);
        }

        const row = document.createElement('div');
        row.className = `conv-row ${isMine ? 'is-mine' : 'is-theirs'}`;
        row.dataset.id = msg.id_messages;

        const time = date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

        row.innerHTML = `
            <div class="conv-bubble-wrap">
                <div class="conv-bubble"></div>
                <div class="conv-time">${time}</div>
            </div>
        `;
        row.querySelector('.conv-bubble').innerHTML = escapeHtml(msg.contenu).replace(/\n/g, '<br>');

        thread.appendChild(row);
        lastId = Math.max(lastId, parseInt(msg.id_messages, 10));
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const contenu = input.value.trim();
        if (!contenu) return;

        sendBtn.disabled = true;

        try {
            const response = await fetch('../api/messages/send_message.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ receiver_id: otherId, contenu }),
            });
            const data = await response.json();

            if (!response.ok || data.error) {
                alert(data.error || 'Erreur lors de l’envoi du message');
                return;
            }

            appendMessage(data.message, true);
            input.value = '';
            charCount.textContent = '0 / 255';
            charCount.classList.remove('is-warning');
            input.style.height = 'auto';
            scrollToBottom();
        } catch (err) {
            alert('Erreur réseau, le message n’a pas pu être envoyé.');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';

        const len = this.value.length;
        charCount.textContent = `${len} / 255`;
        charCount.classList.toggle('is-warning', len > 230);
    });

    async function pollNewMessages() {
        try {
            const response = await fetch(`../api/messages/get_new_messages.php?with=${otherId}&after_id=${lastId}`, {
                credentials: 'same-origin',
            });
            if (!response.ok) return;

            const data = await response.json();
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach((msg) => {
                    if (parseInt(msg.id_messages, 10) <= lastId) return;
                    const isMine = parseInt(msg.sender_id, 10) === <?= $userId ?>;
                    appendMessage(msg, isMine);
                });
                scrollToBottom();
            }
        } catch (err) {
            console.error('Erreur de polling de la conversation :', err);
        }
    }

    scrollToBottom();
    input.focus();
    setInterval(pollNewMessages, 5000);
  </script>
  <?php require_once __DIR__ . '/../components/messages.php'; ?>
  </body>
</html>