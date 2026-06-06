<?php

$alerts = [
  'success' => [
    'registered' => 'Compte créé avec succès !',
    'captcha_deleted' => 'Captcha supprimé !',
    'captcha_updated' => 'Captcha mis à jour !',
    'captcha_created' => 'Captcha créé !',
  ],

  'error' => [
    'no_access' => "Vous n'avez pas accès à cette page !",
    'database_error' => 'Erreur avec la connexion DB',
  ],

  'warning' => [
    'missing_fields' => 'Veuillez remplir tous les champs !',
    'captcha_incorrect' => 'Captcha incorrect !',
    'password_mismatch' => 'Les mots de passe ne correspondent pas !',
    'email_exists' => 'Cet email est déjà utilisé !',
    'deactivated_account' => 'Ce compte est désactivé !',
    'incorrect_password' => 'Mot de passe incorrect !',
    'no_account' => 'Aucun compte trouvé avec cet email !',
    'invalid_json' => 'La réponse doit être un JSON valide (array)',
  ],

  'info' => [
    'logged' =>'Connecté en tant que <strong>' . htmlspecialchars($_SESSION['user']['pseudo'] ?? '') . '</strong> !'
  ]
];

foreach (['success', 'error', 'warning', 'info'] as $type) {
  if (!isset($_GET[$type])) {
      continue;
  }
  $key = $_GET[$type];
  $message = $alerts[$type][$key] ?? 'Erreur sur la popup';

  echo createAlert($message, $type === 'error' ? 'danger' : $type);
  break;
}

function createAlert($message, $type = 'success') {
  $id = 'alert_' . uniqid();
?>
  <link rel="stylesheet" href="/styles/alert.css">
  <div id="<?= $id ?>" class="custom-alert alert alert-<?= $type ?>" role="alert"><?= $message ?></div>
  <script>
    setTimeout(function() {
      var el = document.getElementById('<?= $id ?>');
      if (el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px)';
        setTimeout(() => el.remove(), 500);
      }
    }, 2000);
  </script>
<?php }; ?>