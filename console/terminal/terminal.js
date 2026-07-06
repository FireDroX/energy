const verdictSecret = document.getElementById("verdict-secret");
const input = document.getElementById("terminal-command");

let clickCount = 0;
let clickTimeout = null;

verdictSecret.addEventListener("click", () => {
  clickCount++;

  clearTimeout(clickTimeout);

  if (clickCount === 3) {
    clickCount = 0;

    openTerminal();

    return;
  }

  clickTimeout = setTimeout(() => {
    clickCount = 0;
  }, 1000);
});

function openTerminal() {
  document.getElementById("terminal-modal").classList.add("show");

  input.focus();
}

input.addEventListener("keydown", (e) => {
  if (e.key !== "Enter") return;

  const command = input.value.trim();

  input.value = "";

  switch (command) {
    case "sudo monster":
      startCharging();
      break;

    default:
      alert("Commande inconnue.");
      break;
  }
});

async function startCharging() {
  const output = document.getElementById("terminal-output");

  input.disabled = true;

  const messages = [
    "[ OK ] Booting MonsterOS 1.0",
    "[ OK ] Mounting /monster",
    "[ OK ] Loading kernel modules",
    "[ OK ] Starting Monster Engine",
    "[ OK ] Injecting Taurine",
    "[ OK ] Loading Caffeine",
    "[ OK ] Unlocking Developer Features",
    "[ OK ] Initializing AI Core",
    "[ OK ] Bypassing FDA Regulations",
    "[ OK ] Starting Developer Mode",
  ];

  for (const message of messages) {
    output.innerHTML += message + "\n";

    output.scrollTop = output.scrollHeight;

    await new Promise((resolve) => setTimeout(resolve, 450));
  }

  await new Promise((resolve) => setTimeout(resolve, 800));

  document.getElementById("terminal-modal").classList.remove("show");
  document.getElementById("charging-screen").classList.add("show");

  const timer = document.getElementById("charging-timer");
  const bar = document.getElementById("charging-progress-bar");
  const percent = document.getElementById("charging-percent");

  let totalSeconds = 300;
  const maxSeconds = 300;

  const interval = setInterval(() => {
    totalSeconds--;

    if (totalSeconds < 0) {
      totalSeconds = 0;
    }

    const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, "0");
    const seconds = String(totalSeconds % 60).padStart(2, "0");

    timer.textContent = `${minutes}:${seconds}`;

    const progress = ((maxSeconds - totalSeconds) / maxSeconds) * 100;

    bar.style.width = progress + "%";
    percent.textContent = Math.round(progress) + "%";

    if (totalSeconds === 0) {
      bar.style.width = "100%";
      percent.textContent = "100%";
      timer.textContent = "00:00";

      clearInterval(interval);

      setTimeout(() => {
        document.getElementById("charging-screen").classList.remove("show");
      }, 500);
    }
  }, 10);
}
