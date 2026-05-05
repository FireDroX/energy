document.getElementById("fetch").addEventListener("click", () => {
  fetch("api/users/get.php")
    .then((response) => response.json())
    .then((data) => console.log(data));
});
