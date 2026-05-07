<?php 

session_start();

?>

<link rel="stylesheet" href="/components/navbar.css">

<nav class="navbar">
  <a class="navbar-logo" href="/"><img src="/favicon.png" alt="Logo"></a>
  <ul class="navbar-content">
    <li><a href="/">Home</a></li>
    <li><a href="/search">Recherche</a></li>
    <li><a href="/">Contact</a></li>
    <?php if(!isset($_SESSION['user'])) { ?>
      <li><a href="/login">Login</a></li>
    <?php } ?>
  </ul>

  <div class="navbar-dropdown dropdown">
    <?php if(isset($_SESSION['user'])) { ?>
      <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <?= $_SESSION['user']['pseudo'] ?>
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="/account">Compte</a></li>
        <?php if($_SESSION['user']['role'] == 1 || $_SESSION['user']['role'] == 3) { ?>
          <li><a class="dropdown-item" href="/">Panel</a></li>
        <?php } ?>
        <li><a class="dropdown-item" href="/logout">Déconnexion</a></li>
      </ul>
    <?php } ?>
  </div>
</nav>