<?php

session_start();

if (isset($_SESSION['user'])) {
    echo "Connecté en tant que : " . $_SESSION['user']['pseudo'];
} else {
    echo "Utilisateur non connecté";
}
?>

<nav class="navbar navbar-dark bg-dark navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Monster Energy</a>

    <div class="collapse navbar-collapse center" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/search">Search</a>
        </li>

        <?php if (isset($_SESSION['user'])): ?>

          <li class="nav-item">
            <a class="nav-link" href="/account">Compte</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="/logout">Déconnexion</a>
          </li>

        <?php else: ?>

          <li class="nav-item">
            <a class="nav-link" href="/login">Login</a>
          </li>

        <?php endif; ?>

        <li class="nav-item">
          <a class="nav-link" href="/">Contact</a>
        </li>

      </ul>
    </div>
  </div>
</nav>
