let selectedTags = [];
let allMonsters = [];

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
          <h4>${monster.nom.replaceAll("_", " ").toUpperCase()}</h4>
        </div>
      `;

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

getMonsters();
getTags();
