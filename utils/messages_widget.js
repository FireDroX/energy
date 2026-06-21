const root = document.getElementById("msg-widget-root");
const bubble = document.getElementById("msg-bubble");
const overlay = document.getElementById("msg-overlay");
const closeBtn = document.getElementById("msg-close");
const badge = document.getElementById("msg-badge");
const msgList = document.getElementById("msg-list");

const newToggleBtn = document.getElementById("msg-new-toggle");
const newCancelBtn = document.getElementById("msg-new-cancel");
const newForm = document.getElementById("msg-new-form");
const newRecipient = document.getElementById("msg-new-recipient");
const newContent = document.getElementById("msg-new-content");
const newCharCount = document.getElementById("msg-new-char-count");
const newError = document.getElementById("msg-new-error");
const newSubmitBtn = document.getElementById("msg-new-submit");

function openModal() {
  root.classList.add("is-open");
  bubble.setAttribute("aria-expanded", "true");
  document.addEventListener("keydown", closeOnEscape);
}

function closeModal() {
  root.classList.remove("is-open");
  bubble.setAttribute("aria-expanded", "false");
  document.removeEventListener("keydown", closeOnEscape);
  showConversationList();
}

function closeOnEscape(event) {
  if (event.key === "Escape") {
    closeModal();
  }
}

function toggleModal() {
  if (root.classList.contains("is-open")) {
    closeModal();
  } else {
    openModal();
  }
}

function showNewMessageForm() {
  root.classList.add("is-composing");
  newRecipient.focus();
}

function showConversationList() {
  root.classList.remove("is-composing");
  newError.textContent = "";
  newForm.reset();
  newCharCount.textContent = "0 / 255";
}

function toggleNewMessageForm() {
  if (root.classList.contains("is-composing")) {
    showConversationList();
  } else {
    showNewMessageForm();
  }
}

function formatRelativeTime(dateText) {
  const date = new Date(dateText.replace(" ", "T"));
  const secondsAgo = Math.round((Date.now() - date.getTime()) / 1000);

  if (secondsAgo < 60) return "à l’instant";
  if (secondsAgo < 3600) return `il y a ${Math.floor(secondsAgo / 60)} min`;
  if (secondsAgo < 86400) return `il y a ${Math.floor(secondsAgo / 3600)} h`;
  if (secondsAgo < 172800) return "hier";

  return date.toLocaleDateString("fr-FR", { day: "numeric", month: "short" });
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function markConversationAsRead(senderId) {
  fetch("/api/messages/mark_as_read.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ sender_id: senderId }),
  });
}

function createConversationItem(conv) {
  const link = document.createElement("a");
  link.className = "msg-item" + (conv.non_lus > 0 ? " is-unread" : "");
  link.href = `/conversation?with=${conv.id_users}`;
  link.dataset.senderId = conv.id_users;

  const youPrefix = conv.is_from_them
    ? ""
    : '<span class="msg-item-you">Vous : </span>';
  const firstLetter = conv.pseudo.charAt(0).toUpperCase();

  link.innerHTML = `
        <span class="msg-item-avatar">${escapeHtml(firstLetter)}</span>
        <span class="msg-item-body">
            <span class="msg-item-top">
                <span class="msg-item-name">${escapeHtml(conv.pseudo)}</span>
                <span class="msg-item-time">${formatRelativeTime(conv.derniere_date)}</span>
            </span>
            <span class="msg-item-preview">${youPrefix}${escapeHtml(conv.preview)}</span>
        </span>
        ${conv.non_lus > 0 ? '<span class="msg-item-dot"></span>' : ""}
    `;

  link.addEventListener("click", () => markConversationAsRead(conv.id_users));
  return link;
}

function showConversations(conversations) {
  msgList.innerHTML = "";

  if (conversations.length === 0) {
    msgList.innerHTML =
      '<div id="msg-empty"><p>Aucune conversation pour le moment.</p></div>';
    return;
  }

  conversations.forEach((conv) => {
    msgList.appendChild(createConversationItem(conv));
  });
}

async function refreshConversationList() {
  const response = await fetch("/api/messages/get_conversations.php", {
    credentials: "same-origin",
  });

  if (!response.ok) return;

  const data = await response.json();
  if (data.error) return;

  showConversations(data.conversations);
}

function updateBadge(count) {
  badge.textContent = count > 9 ? "9+" : count;

  if (count > 0) {
    badge.classList.add("is-visible");
    badge.classList.remove("is-pulsing");
    requestAnimationFrame(() => badge.classList.add("is-pulsing"));
  } else {
    badge.classList.remove("is-visible");
  }
}

async function sendNewMessage(event) {
  event.preventDefault();

  const receiverId = newRecipient.value;
  const contenu = newContent.value.trim();

  if (!receiverId) {
    newError.textContent = "Choisis un destinataire.";
    return;
  }
  if (!contenu) {
    newError.textContent = "Le message ne peut pas être vide.";
    return;
  }

  newSubmitBtn.disabled = true;
  newError.textContent = "";

  try {
    const response = await fetch("/api/messages/send_message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ receiver_id: receiverId, contenu }),
    });
    const data = await response.json();

    if (!response.ok || data.error) {
      newError.textContent = "Erreur lors de l’envoi du message.";
      return;
    }

    window.location.href = `/conversation?with=${receiverId}`;
  } catch (error) {
    newError.textContent = "Erreur réseau, réessaie.";
  } finally {
    newSubmitBtn.disabled = false;
  }
}

bubble.addEventListener("click", toggleModal);
overlay.addEventListener("click", closeModal);
closeBtn.addEventListener("click", closeModal);
newToggleBtn.addEventListener("click", toggleNewMessageForm);
newCancelBtn.addEventListener("click", showConversationList);
newForm.addEventListener("submit", sendNewMessage);

newContent.addEventListener("input", () => {
  const length = newContent.value.length;
  newCharCount.textContent = `${length} / 255`;
  newCharCount.classList.toggle("is-warning", length > 230);
});

document.querySelectorAll(".msg-item-time").forEach((el) => {
  el.textContent = formatRelativeTime(el.dataset.date);
});

window.msgWidget = {
  updateBadge,
  refreshList: refreshConversationList,
  open: openModal,
  close: closeModal,
};
