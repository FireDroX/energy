let croppie;

const input = document.getElementById("avatar-upload");
const container = document.getElementById("avatar-crop");

croppie = new Croppie(container, {
  viewport: {
    width: 220,
    height: 220,
    type: "circle",
  },

  boundary: {
    width: 320,
    height: 320,
  },

  enableZoom: true,
  showZoomer: true,
  enableOrientation: false,
});

input.addEventListener("change", function (e) {
  const reader = new FileReader();

  reader.onload = function (event) {
    croppie.bind({
      url: event.target.result,
    });
  };

  reader.readAsDataURL(e.target.files[0]);
});

document.getElementById("save-avatar").addEventListener("click", async () => {
  const blob = await croppie.result({
    type: "blob",
    size: {
      width: 256,
      height: 256,
    },
    format: "webp",
    quality: 1,
  });

  const data = new FormData();

  data.append("avatar", blob, "avatar.webp");

  await fetch("upload_avatar.php", {
    method: "POST",
    body: data,
  });

  location.reload();
});
