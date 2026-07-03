const input = document.getElementById("avatar-upload");
const fileName = document.getElementById("selected-file");
const preview = document.getElementById("avatar-preview");
const placeholder = document.getElementById("avatar-placeholder");

input.addEventListener("change", () => {
  if (!input.files.length) {
    return;
  }

  const file = input.files[0];

  fileName.textContent = file.name;

  const reader = new FileReader();

  reader.onload = function (e) {
    preview.src = e.target.result;
    preview.hidden = false;

    placeholder.hidden = true;
  };

  reader.readAsDataURL(file);
});
