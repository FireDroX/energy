<?php

$alerts = [
  'success' => [
    'registered' => 'Compte créé avec succès !',
    'captcha_deleted' => 'Captcha supprimé !',
    'captcha_updated' => 'Captcha mis à jour !',
    'captcha_created' => 'Captcha créé !',
    'user_desactivated' => 'Utilisateur désactivé / modifié !',
    'user_activated' => 'Utilisateur réactivé / modifié !',
    'user_updated' => 'Utilisateur mis à jour !',
    'user_created' => 'Utilisateur créé !',
    'avatar_deleted' => 'Avatar supprimé !',
    'tags_updated' => 'Tags mis à jour !',
    'tag_created' => 'Tag créé !',
    'monster_updated' => 'Monster mis à jour !',
    'monster_created' => 'Monster créé !',
    'mail_sent' => 'Un email de confirmation vous à été envoyé ! Vérifiez vos spams si vous ne le voyez pas.',
    'newsletter_activate' => 'Activation de la newsletter',
    'newsletter_deactivate' => 'Désactivation de la newsletter',
    'message_sent' => 'Votre commentaire à bien été envoyé !',
    'message_deleted' => 'Votre commentaire à bien été supprimé !',
    'note_updated' => 'Votre note à bien été mise à jour !',
    'note_created' => 'Votre note à bien été créée !',
    'note_deleted' => 'Votre note à bien été supprimée !',
    'comment_pinned' => 'Commentaire épinglé !',
    'comment_unpinned' => 'Commentaire désépinglé !',
    'color_changed' => 'Couleur changéé !'
  ],

  'error' => [
    'no_access' => "Vous n'avez pas accès à cette page !",
    'database_error' => 'Erreur avec la connexion DB',
    'invalid_params' => 'Paramètres invalides !',
    'mail_send_failed' => 'Erreur avec l\'envoie de l\'email'
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
    'non_existant_role' => 'Ce role n\'existe pas !',
    'tag_exists' => 'Ce tag est déjà utilisé !',
    'session_expired' => 'Session expirée !',
    'activated_account' => 'Ce compte n\'est pas activé, vérifie ton mail !',
    'forbidden' => 'Vous n\'êtes pas autorisé à faire ceci !',
    'color_not_unlocked' => 'Vous n\'avez pas cette couleurs !',
    'avatar_oversized' => 'L\'image dépasse 2 Mo.',
    'avatar_invalid_format' => 'Format d\'image non autorisé.',
    'avatar_unreadable' => 'Impossible de lire l\'image.',
    'avatar_no_file' => 'Aucun fichier sélectionné.'
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