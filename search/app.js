let selectedTags = [],
  allMonsters = [],
  searchText = "",
  debounceTimeout;

const formatName = (name) => name.replaceAll("_", " ").toUpperCase();

const getMonsters = async () => {
  try {
    if (allMonsters.length === 0) {
      const request = await fetch("/api/monsters/get.php");
      allMonsters = await request.json();
    }

    let res = [...allMonsters];

    if (selectedTags.length > 0) {
      res = res.filter((monster) =>
        selectedTags.every((tag) => monster.tags.includes(tag)),
      );
    }

    if (searchText.trim() !== "") {
      const search = searchText.trim().toUpperCase();

      res = res.filter((monster) => formatName(monster.nom).includes(search));
    }

    const el = document.getElementById("monstersCaroussel");
    el.innerHTML = "";

    res.forEach((monster) => {
      const element = document.createElement("article");
      element.className = "monster-container";

      element.innerHTML = `
        <div class="monster-image">
          <img src="${monster.image}" alt="${monster.nom}" />
        </div>

        <div class="monster-text">
          <h4>${formatName(monster.nom)}</h4>
        </div>

        <div class="favorite-btn ${monster.favorite ? "active" : ""}">
          <svg viewBox="0 0 24 24">
            <path d="M12 21s-7-4.35-10-9c-2.5-3.9-.5-9 4-9 2.4 0 4 1.6 6 3.6C14 4.6 15.6 3 18 3c4.5 0 6.5 5.1 4 9-3 4.65-10 9-10 9z"/>
          </svg>
        </div>
      `;

      const favoriteBtn = element.querySelector(".favorite-btn");

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
              monster_id: monster.id_monsters,
            }),
          });

          if (request.status === 401) {
            showLoginPopup();
            return;
          }

          const data = await request.json();

          if (data.success) {
            favoriteBtn.classList.toggle("active");
            monster.favorite = !monster.favorite;
          }
        } catch (err) {
          console.error(err);
        }
      });

      element.addEventListener("click", (e) => {
        if (e.target.closest(".favorite-btn")) {
          return;
        }

        window.location.href = `/monster?name=${monster.nom}`;
      });

      el.appendChild(element);
    });
  } catch (err) {
    console.log(err);
  }
};

const getTags = async () => {
  try {
    const request = await fetch("/api/monsters/tags.php");
    const res = await request.json();
    const el = document.getElementById("monstersTags");
    el.innerHTML = "";

    res.forEach((tag) => {
      const element = document.createElement("li");
      element.textContent = tag.nom.toUpperCase();

      element.addEventListener("click", () => {
        const tagName = tag.nom;

        if (selectedTags.includes(tagName)) {
          selectedTags = selectedTags.filter((t) => t !== tagName);
          element.classList.toggle("tag-active");
        } else {
          selectedTags.push(tagName);
          element.classList.toggle("tag-active");
        }

        getMonsters();
      });

      el.appendChild(element);
    });
  } catch (err) {
    console.log(err);
  }
};

document.getElementById("monsterSearch").addEventListener("input", (e) => {
  clearTimeout(debounceTimeout);

  debounceTimeout = setTimeout(() => {
    searchText = e.target.value;
    getMonsters();
  }, 150);
});

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

getMonsters();
getTags();
