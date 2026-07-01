<link rel="stylesheet" href="/styles/navbar.css">
<script src="/utils/easter_eggs.js" defer></script>
<nav class="monster-navbar">
  <div class="navbar-left">
    <h4 class="monster-logo">
      <a href="/">
        <img class="navbar-logo" src="/favicon.png" alt="Logo">
        <span>Monster Energy</span>
      </a>
    </h4>
    <ul class="monster-links me-auto">
      <li><a href="/"><span>Home</span></a></li>
      <li><a href="/search"><span>Recherche</span></a></li>
      <li><a href="/contact"><span>Contact</span></a></li>
      <li><a href="/leaderboard"><span>Classement</span></a></li>

      <?php if (isset($_SESSION['user'])) { ?>
        <li><a href="/roulette"><span>Roulette</span></a></li>
      <?php } ?>
    </ul>
  </div>
  <ul class="monster-links">
  <?php if (isset($_SESSION['user'])) { ?>
  <div class="dropdown monster-dropdown">
    <button class="btn btn-secondary monster-dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      Compte
    </button>
    <ul class="dropdown-menu monster-dropdown-menu">
      <li><a class="dropdown-item monster-dropdown-item" href="/account"><span>Compte</span></a></li>

      <?php if($_SESSION['user']['role'] == 1 || $_SESSION['user']['role'] == 3) { ?>
        <li><a class="dropdown-item monster-dropdown-item" href="/panel"><span>Panel</span></a></li>
      <?php } ?>

      <li><a class="dropdown-item monster-dropdown-item" href="/logout"><span>Déconnexion</span></a></li>
    </ul>
  </div>
  <?php } else { ?>
    <li><a href="/login"><span>Login</span></a></li>
  <?php } ?>
  </ul>
  <div class="dropdown monster-dropdown monster-dropdown--mobile" id="navbar-mobile-dropdown">
    <button class="btn btn-secondary monster-dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      Navigation
    </button>
    <ul class="dropdown-menu monster-dropdown-menu">
      <li><a class="dropdown-item monster-dropdown-item" href="/"><span>Home</span></a></li>
      <li><a class="dropdown-item monster-dropdown-item" href="/search"><span>Recherche</span></a></li>
      <li><a class="dropdown-item monster-dropdown-item" href="/contact"><span>Contact</span></a></li>
      <li><a class="dropdown-item monster-dropdown-item" href="/leaderboard"><span>Classement</span></a></li>

      <?php if (isset($_SESSION['user'])) { ?>
        <li><a class="dropdown-item monster-dropdown-item" href="/roulette"><span>Roulette</span></a></li>
      <?php } ?>

      <li><hr class="dropdown-divider monster-dropdown-divider"></li>

      <?php if (isset($_SESSION['user'])) { ?>
        <li><a class="dropdown-item monster-dropdown-item" href="/account"><span>Compte</span></a></li>

        <?php if($_SESSION['user']['role'] == 1 || $_SESSION['user']['role'] == 3) { ?>
          <li><a class="dropdown-item monster-dropdown-item" href="/panel"><span>Panel</span></a></li>
        <?php } ?>

        <li><a class="dropdown-item monster-dropdown-item" href="/logout"><span>Déconnexion</span></a></li>
      <?php } else { ?>
        <li><a class="dropdown-item monster-dropdown-item" href="/login"><span>Login</span></a></li>
      <?php } ?>
    </ul>
  </div>
</nav>