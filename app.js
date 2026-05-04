document.getElementById("fetch").addEventListener("click", () => {
  fetch("api/health.php")
    .then((response) => response.json())
    .then((data) => console.log(data));
});
