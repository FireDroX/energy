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

    const interactions = document.querySelector(".interactions-footer");

    const div = document.createElement("div");
    div.className = "interactions-container";

    const liked = document.createElement("div");
    liked.className = `favorite-btn ${res.favorite ? "active" : ""}`;
    liked.innerHTML = `
      <svg viewBox="0 0 24 24">
        <path d="M12 21s-7-4.35-10-9c-2.5-3.9-.5-9 4-9 2.4 0 4 1.6 6 3.6C14 4.6 15.6 3 18 3c4.5 0 6.5 5.1 4 9-3 4.65-10 9-10 9z"/>
      </svg>`;

    const drank = document.createElement("div");
    drank.className = `drank-btn ${res.drank ? "active" : ""}`;
    drank.innerHTML = `<svg viewBox="0 0 512 512"><path d="M238.8 52.3c-2.6.8-6.5 2.5-8.7 4-3 2-6.1 2.9-12.8 3.7-23.2 2.8-42.8 8.1-50 13.6-6.6 5-8.7 11-6.2 17.9 1.1 3 .7 3.7-10 19.9L140 128.2l.2 137.8.3 137.8 3 4.4c1.7 2.3 5.9 6.2 9.5 8.6 5.5 3.8 6.8 5.3 9.7 11.3 4.1 8.3 9.9 13.4 21.8 18.9 20.2 9.4 56.1 15.2 80.2 12.9 46-4.2 73.4-16.1 79.8-34.5.9-2.8 2.8-4.7 8.1-8.3 7.4-5 10.8-9.2 12.4-15 .6-2.3 1-51.3 1-138.6V128.4l-11.4-17.3c-10.9-16.7-11.3-17.5-10-20 7.7-14.7-13.4-26.4-56.1-31.1-7.3-.8-10.5-1.6-12-3-4.1-3.8-9.5-5.3-21.5-5.6-7.6-.3-13.1.1-16.2.9zm27.5 13.8c2.7 1.2 2.8 1.6 2.2 5.4-.3 2.3-1.1 6.3-1.8 9l-1.2 4.8-12.7.1-12.7.1-1.7-8.2c-.9-4.5-1.4-8.8-1.1-9.6.3-.9 2.5-2 4.9-2.6 5.6-1.3 20.5-.7 24.1 1zm-40.9 15.5l1.7 8.6-4.3 4-4.3 4-8.3-1.1c-11.7-1.7-26-5.4-32.2-8.6l-5.3-2.7 2.9-1.9c6.2-4.1 32.6-10.6 44-10.8l4.1-.1 1.7 8.6zM306 76c14.3 2.9 26 7.3 26 9.9 0 2.7-20 8.8-36.5 11.1l-8.1 1.2-3.9-3.6c-2.1-2-4.2-3.6-4.5-3.6-.4 0 .2-4.1 1.3-9.1l2.1-9.1 7 .6c3.9.4 11.4 1.6 16.6 2.6zm-47.5 22.9c3.1.7 1.8.9-6 .8-7.5 0-8.9-.2-5.5-.7 6.3-1 7-1 11.5-.1zm-73.7 6.5c17.6 5.4 37.7 7.6 69.1 7.6 31 0 53.3-2.7 71-8.6 4.1-1.4 7.8-2.1 8.2-1.7.4.4 4.6 6.6 9.3 13.8 5.9 9 8.2 13.3 7.5 14.1-2.8 3.4-28.2 9-52.4 11.6-16.2 1.7-73 1.7-89.5-.1-26.1-2.7-53.9-9.4-52.7-12.5.3-.6 4.4-7.1 9.2-14.4 6.6-10.1 9.1-13.2 10.4-12.8.9.3 5.3 1.6 9.9 3zM159.6 147c14.5 5 39.3 8.5 72.1 10.1 41.7 2.1 92.4-2.4 114.8-10.1l5.5-1.9v255.5l-2.7 2.7c-6.1 5.7-24.6 12.2-44.8 15.7-34.8 5.9-83.1 5-114-2.2-17.5-4-30.2-9.4-35-14.8-2-2.2-2-3.8-2.2-129.6-.2-70.1-.1-127.4 0-127.4.2 0 3 .9 6.3 2zm46.1 286.5c25.9 3.8 58.9 4.2 86.8 1 11-1.3 28.1-4.4 33-6l2-.7-2 1.7c-11.8 9.8-44.5 17.6-73.5 17.6-25.6 0-59.8-8.2-71-17-2.5-2-2.4-2 6.2-.1 4.8 1.1 13.1 2.6 18.5 3.5z"/></svg>`;

    const drankText = document.createElement("small");
    drankText.className = `drank-text ${res.drank ? "active" : ""}`;
    drankText.textContent = `${res.drank ? "Bu aujourd'hui" : "Pas bu aujourd'hui"}`;

    const stars = document.createElement("div");
    stars.className = "stars-container";

    for (let i = 0; i < 5; i++) {
      const star = document.createElement("div");
      console.log(i, res.user_note, res.user_note > i);

      star.className = "star";
      star.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class=${res.user_note > i ? "active" : "innactive"}><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>`;

      stars.appendChild(star);

      star.addEventListener("click", async (e) => {
        e.preventDefault();
        e.stopPropagation();

        try {
          const request = await fetch("/api/monsters/rate.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              monster_id: res.id_monsters,
              note: i + 1,
            }),
          });

          if (request.status === 401) {
            showLoginPopup();
            return;
          }

          const data = await request.json();

          if (data.success) {
            location.href = `/monster/?name=${monster_name}&success=note_updated`;
          }
        } catch (err) {
          console.error(err);
        }
      });
    }

    div.append(liked, drank, drankText);

    interactions.prepend(div, stars);
    section.appendChild(interactions);

    const favoriteBtn = div.querySelector(".favorite-btn");

    favoriteBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      e.stopPropagation();

      try {
        const request = await fetch("/api/monsters/toggle_favorite.php", {
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

    const drankBtn = div.querySelector(".drank-btn");

    drankBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      e.stopPropagation();

      try {
        const request = await fetch("/api/monsters/toggle_drank.php", {
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
          drankBtn.classList.toggle("active");
          drankText.classList.toggle("active");
          res.drank = !res.drank;
          drankText.textContent = `${res.drank ? "Bu aujourd'hui" : "Pas bu aujourd'hui"}`;
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
