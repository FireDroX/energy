document.getElementById("fetch").addEventListener("click", () => {
  fetch("api/health")
    .then((response) => response.json())
    .then((data) => console.log(data));
});
