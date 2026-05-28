<?php function renderPanel($svg, $titre, $url, $disabled = false) { ?>
<a 
  href="<?= $disabled ? '/panel' : htmlspecialchars($url) ?>" 
  class="panel <?= $disabled ? 'panel-disabled' : '' ?>" 
  >
  <span class="panel-icon"><?= $svg ?></span>
  <h3 class="panel-titre"><?= htmlspecialchars($titre) ?></h3>
</a>
<?php } ?>