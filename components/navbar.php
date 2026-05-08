<?php

session_start();

if (isset($_SESSION['user'])) {
    echo "Connecté en tant que : " . $_SESSION['user']['pseudo'];
} else {
    echo "Utilisateur non connecté";
}
?>

<link rel="stylesheet" href="/css/navbar_style.css">

<nav class="monster-navbar">
  <div class="monster-logo">
    <a href="/">Monster Energy</a>
  </div>

  <ul class="monster-links me-auto">

    <li>
      <a href="/">Home</a>
    </li>

    <li>
      <a href="/search">Search</a>
    </li>

    <li>
      <a href="/">Contact</a>
    </li>
    </ul>
  
    <ul class="monster-links">
    <?php if (isset($_SESSION['user'])): ?>

      <li>
        <a href="/account">Compte</a>
      </li>

      <li>
        <a href="/logout">Déconnexion</a>
      </li>

    <?php else: ?>

      <li>
        <a href="/login">Login</a>
      </li>

    <?php endif; ?>
  </ul>
</nav>