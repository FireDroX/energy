const POLL_INTERVAL = 15000;
const STORAGE_KEY = "nb_messages_non_lus";
const CHECK_URL = "/api/messages/check_messages.php";

const notificationSound = new Audio(
  "https://cdn.pixabay.com/download/audio/2025/09/02/audio_4e70a465f7.mp3",
);

function playNotificationSound() {
  notificationSound.currentTime = 0;
  notificationSound.play().catch(() => {});
}

function drawFaviconBadge(image, count) {
  const canvas = document.createElement("canvas");
  canvas.width = 64;
  canvas.height = 64;

  const ctx = canvas.getContext("2d");
  ctx.drawImage(image, 0, 0, 64, 64);

  if (count > 0) {
    ctx.fillStyle = "#f23f43";
    ctx.beginPath();
    ctx.arc(50, 14, 14, 0, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = "white";
    ctx.font = "bold 18px Arial";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(count > 9 ? "9+" : String(count), 50, 14);
  }

  return canvas.toDataURL("image/png");
}

function getFaviconLink() {
  let link = document.querySelector("link[rel='shortcut icon']");

  if (!link) {
    link = document.createElement("link");
    link.rel = "shortcut icon";
    document.head.appendChild(link);
  }

  return link;
}

function updateFavicon(count) {
  const image = new Image();
  image.onload = () => {
    const favicon = getFaviconLink();
    favicon.href = drawFaviconBadge(image, count);
  };
  image.src = "/favicon.png";
}

async function checkNewMessages() {
  try {
    const response = await fetch(CHECK_URL, { credentials: "same-origin" });
    if (!response.ok) return;

    const data = await response.json();
    const nbActuel = data.nb_non_lus ?? 0;
    const nbPrecedent = parseInt(localStorage.getItem(STORAGE_KEY) || "0", 10);

    if (nbActuel > nbPrecedent) {
      playNotificationSound();
    }

    updateFavicon(nbActuel);
    localStorage.setItem(STORAGE_KEY, String(nbActuel));

    if (window.msgWidget) {
      window.msgWidget.updateBadge(nbActuel);

      if (nbActuel !== nbPrecedent) {
        window.msgWidget.refreshList();
      }
    }
  } catch (error) {
    console.error("Erreur lors de la vérification des messages :", error);
  }
}

checkNewMessages();
setInterval(checkNewMessages, POLL_INTERVAL);
