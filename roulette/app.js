const slot = document.getElementById("roulette-slot");
const button = document.getElementById("spinBtn");
const popup = document.getElementById("roulette-popup");
const popupImg = document.getElementById("popup-img");
const popupName = document.getElementById("popup-name");
const popupClose = document.getElementById("popup-close");
const selectRoulette = document.getElementById("select-roulette");

selectRoulette.addEventListener("change", (e) => {
  document.location.href = `/roulette?mode=${e.target.value}`;
})

button.addEventListener("click", async () => {
  if (!monsters || monsters.length === 0) {
    showRouletteEmptyPopup("Tu n'as aucune Monster favorite pour le moment.");
    return;
  }

  button.disabled = true;
  slot.classList.remove("winner-animation");

  let delay = 25;
  const tours = 45;

  for (let i = 0; i < tours; i++) {
    const monster = monsters[Math.floor(Math.random() * monsters.length)];

    slot.innerHTML = `
      <img src="${monster.image}">
      <h2>${monster.nom.replaceAll("_", " ")}</h2>
    `;

    await new Promise((resolve) => setTimeout(resolve, delay));

    if (i < 30) {
      delay += 1;
    } else {
      delay += 8;
    }
  }

  const finalMonster = monsters[Math.floor(Math.random() * monsters.length)];

  slot.innerHTML = `
    <img src="${finalMonster.image}" class="winner">
    <h2>${finalMonster.nom.replaceAll("_", " ")}</h2>
  `;

  slot.classList.add("winner-animation");

  popupImg.src = finalMonster.image;
  popupName.textContent = finalMonster.nom.replaceAll("_", " ");

  popup.classList.add("active");

  button.disabled = false;
});

popupClose.addEventListener("click", () => {
  popup.classList.remove("active");
});

popup.addEventListener("click", (e) => {
  if (e.target === popup) {
    popup.classList.remove("active");
  }
});

function showRouletteEmptyPopup(message) {
  const popup = document.createElement("div");
  popup.className = "roulette-empty-popup-overlay";

  popup.innerHTML = `
    <div class="roulette-empty-popup">
      <h3>Aucune Monster trouvée</h3>
      <p>${message}</p>

      <div class="popup-actions">
        <a href="/search" class="popup-login-btn">
          Voir les Monsters
        </a>

        <button class="popup-close-btn">
          Fermer
        </button>
      </div>
    </div>
  `;

  document.body.appendChild(popup);

  popup.querySelector(".popup-close-btn").addEventListener("click", () => {
    popup.remove();
  });

  popup.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.remove();
    }
  });
}
