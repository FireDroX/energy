const page = document.querySelector('.container');
const otherId = page.dataset.otherId;
let lastMessageId = parseInt(page.dataset.lastId, 10) || 0;

const thread = document.getElementById('conv-thread');
const emptyMessage = document.getElementById('conv-empty');
const form = document.getElementById('conv-composer');
const input = document.getElementById('conv-input');
const sendBtn = document.getElementById('conv-send');
const charCount = document.getElementById('conv-char-count');

function getDayLabel(date) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);

    const compareDate = new Date(date);
    compareDate.setHours(0, 0, 0, 0);

    if (compareDate.getTime() === today.getTime()) return 'Aujourd’hui';
    if (compareDate.getTime() === yesterday.getTime()) return 'Hier';

    return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
}

function scrollToBottom() {
    thread.scrollTop = thread.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getLastDayDividerText() {
    const dividers = thread.querySelectorAll('.conv-day-divider');
    if (dividers.length === 0) return null;
    return dividers[dividers.length - 1].textContent;
}

function addDayDividerIfNeeded(date) {
    const label = getDayLabel(date);
    if (getLastDayDividerText() === label) return;

    const divider = document.createElement('div');
    divider.className = 'conv-day-divider';
    divider.textContent = label;
    thread.appendChild(divider);
}

function addMessageToThread(message, isMine) {
    if (emptyMessage) emptyMessage.remove();

    const date = new Date(message.date_envoie.replace(' ', 'T'));
    addDayDividerIfNeeded(date);

    const row = document.createElement('div');
    row.className = `conv-row ${isMine ? 'is-mine' : 'is-theirs'}`;
    row.dataset.id = message.id_messages;

    const time = date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    const safeContent = escapeHtml(message.contenu).replace(/\n/g, '<br>');

    row.innerHTML = `
        <div class="conv-bubble-wrap">
            <div class="conv-bubble">${safeContent}</div>
            <div class="conv-time">${time}</div>
        </div>
    `;

    thread.appendChild(row);
    lastMessageId = Math.max(lastMessageId, parseInt(message.id_messages, 10));
}

function resetComposer() {
    input.value = '';
    input.style.height = 'auto';
    charCount.textContent = '0 / 255';
    charCount.classList.remove('is-warning');
}

async function sendMessage(event) {
    event.preventDefault();

    const contenu = input.value.trim();
    if (!contenu) return;

    sendBtn.disabled = true;

    try {
        const response = await fetch('/api/messages/send_message.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ receiver_id: otherId, contenu }),
        });
        const data = await response.json();

        if (!response.ok || data.error) {
            alert('Erreur lors de l’envoi du message.');
            return;
        }

        addMessageToThread(data.message, true);
        resetComposer();
        scrollToBottom();
    } catch (error) {
        alert('Erreur réseau, le message n’a pas pu être envoyé.');
    } finally {
        sendBtn.disabled = false;
        input.focus();
    }
}

function growTextarea() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 120) + 'px';

    const length = input.value.length;
    charCount.textContent = `${length} / 255`;
    charCount.classList.toggle('is-warning', length > 230);
}

async function checkForNewMessages() {
    try {
        const url = `/api/messages/get_new_messages.php?with=${otherId}&after_id=${lastMessageId}`;
        const response = await fetch(url, { credentials: 'same-origin' });
        if (!response.ok) return;

        const data = await response.json();
        if (!data.messages || data.messages.length === 0) return;

        data.messages.forEach((message) => {
            if (parseInt(message.id_messages, 10) <= lastMessageId) return;
            const isMine = parseInt(message.sender_id, 10) === currentUserId;
            addMessageToThread(message, isMine);
        });

        scrollToBottom();
    } catch (error) {
        console.error('Erreur de polling de la conversation :', error);
    }
}

form.addEventListener('submit', sendMessage);
input.addEventListener('input', growTextarea);

input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        form.requestSubmit();
    }
});

scrollToBottom();
input.focus();
setInterval(checkForNewMessages, 5000);