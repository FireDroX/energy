function updateFavicon(count) {
  const canvas = document.createElement("canvas");
  canvas.width = 64;
  canvas.height = 64;

  const ctx = canvas.getContext("2d");

  const img = new Image();

  img.onload = () => {
    // favicon de base
    ctx.drawImage(img, 0, 0, 64, 64);

    if (count > 0) {
      // rond rouge
      ctx.fillStyle = "#f23f43";
      ctx.beginPath();
      ctx.arc(50, 14, 14, 0, Math.PI * 2);
      ctx.fill();

      // texte
      ctx.fillStyle = "white";
      ctx.font = "bold 18px Arial";
      ctx.textAlign = "center";
      ctx.textBaseline = "middle";

      const text = count > 9 ? "9+" : String(count);

      ctx.fillText(text, 50, 14);
    }

    let favicon = document.querySelector("link[rel='shortcut icon']");

    if (!favicon) {
      favicon = document.createElement("link");
      favicon.rel = "shortcut icon";
      document.head.appendChild(favicon);
    }

    favicon.href = canvas.toDataURL("image/png");
  };

  img.src = "/favicon.png";
}

const sound = new Audio("https://cdn.pixabay.com/download/audio/2025/09/02/audio_4e70a465f7.mp3");

function playNotificationSound() {
  sound.currentTime = 0;
  sound.play();
}

const POLL_INTERVAL = 15000;
const STORAGE_KEY = 'nb_messages_non_lus';
const CHECK_URL = '/../api/messages/check_messages.php';

async function checkNewMessages() {
  try {
    const response = await fetch(CHECK_URL, { credentials: 'same-origin' });

    if (!response.ok) {
      console.error('Erreur HTTP lors du check des messages :', response.status);
      return;
    }

    const data = await response.json();
    const nbActuel = data.nb_non_lus ?? 0;
    const nbPrecedent = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);

    if (nbActuel > nbPrecedent) {
      playNotificationSound();
    }

    if (window.msgWidget) {
      window.msgWidget.updateBadge(nbActuel);
    }

    updateFavicon(nbActuel);
    localStorage.setItem(STORAGE_KEY, String(nbActuel));

  } catch (err) {
    console.error('Erreur lors de la vérification des messages :', err);
  }
}

checkNewMessages();
setInterval(checkNewMessages, POLL_INTERVAL);