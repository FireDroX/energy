const getMonsters = async () => {
  try {
    const request = await fetch("/api/monsters/get.php");
    const res = await request.json();
    const el = document.getElementById("monstersCaroussel");

    res.map((monster) => {
      const element = document.createElement("article");
      element.className = "monster-container";

      element.innerHTML = `
        <div class="monster-image" >
          <img src="${monster.image}" alt="${monster.nom}" />
        </div>
        <div class="monster-text" >
          <h3>${monster.nom.replaceAll("_", " ").toUpperCase()}</h3>
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

    res.map((tag) => {
      const element = document.createElement("li");
      element.innerHTML = tag.nom.toUpperCase();

      el.appendChild(element);
    });
  } catch (error) {
    console.log(err);
  }
};

getMonsters();
getTags();
