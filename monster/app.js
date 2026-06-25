const section = document.querySelector(".monster-header");
const monster_name = new URLSearchParams(window.location.search).get("name");

let allMonsters = [];

async function getMonster() {
  try {
    if (allMonsters.length === 0) {
      const request = await fetch("/api/monsters/get.php");
      allMonsters = await request.json();
    }

    let res = [...allMonsters].filter((m) => m.nom == monster_name)[0];

    const el = document.createElement("div");
    el.className = `favorite-btn ${res.favorite ? "active" : ""}`;
    el.innerHTML = `
      <svg viewBox="0 0 24 24">
        <path d="M12 21s-7-4.35-10-9c-2.5-3.9-.5-9 4-9 2.4 0 4 1.6 6 3.6C14 4.6 15.6 3 18 3c4.5 0 6.5 5.1 4 9-3 4.65-10 9-10 9z"/>
      </svg>`;

    section.appendChild(el);

    const favoriteBtn = section.querySelector(".favorite-btn");

    favoriteBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      e.stopPropagation();

      try {
        const request = await fetch("/api/favorites/toggle.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            monster_id: res.id_monsters,
          }),
        });

        if (request.status === 401) {
          showLoginPopup();
          return;
        }

        const data = await request.json();

        if (data.success) {
          favoriteBtn.classList.toggle("active");
          res.favorite = !res.favorite;
        }
      } catch (err) {
        console.error(err);
      }
    });
  } catch (err) {
    console.log(err);
  }
}

function showLoginPopup() {
  const popup = document.createElement("div");

  popup.className = "login-popup-overlay";

  popup.innerHTML = `
    <div class="login-popup">
      <h3>Connexion requise</h3>

      <p>
        Vous devez être connecté pour ajouter une Monster à vos favoris.
      </p>

      <div class="popup-actions">
        <a href="/login/" class="popup-login-btn">
          Se connecter
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

getMonster();
