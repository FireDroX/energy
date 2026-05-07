<?php 

session_start(); 
$isLogged = isset($_SESSION['user']);

?>

<nav class="navbar navbar-dark bg-dark navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Monster Energy</a>
    <div class="collapse navbar-collapse center" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/search">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href=<?= $isLogged ? "/profile" : "/login" ?>>
            <?= $isLogged ? "Profile" : "Login" ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/contact">Contact</a>
        </li>
      </ul>
    </div>
  </div>
</nav>