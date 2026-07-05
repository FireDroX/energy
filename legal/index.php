<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/loggers.php';
require_once __DIR__ . '/../utils/database.php';
if (isset($_SESSION['user'])) addLog($pdo, $_SESSION['user']['id'], 'NAVIGATION', 'Utilise ' . $_SERVER['SCRIPT_NAME']);
?>


<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mention & Confidentialité</title>

    <link rel="shortcut icon" href="/favicon.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/styles/home.css">
    <link rel="stylesheet" href="styles.css">
    </head>

    <body>
        <header><?php require_once __DIR__ . '/../components/navbar.php'; ?></header>
        <?php require_once __DIR__ . '/../components/alert.php'; ?>

        <main class="container">
                <div class="home-section-header">
                    <span id="verdict-secret">Mention Légal & Confidentialité</span>
                    <h2>Mention Légale</h2>
                </div>
            <div class="mention-container">
                <article class="mention-card">
                    <h3>
                        Éditeur du site
                    </h3>

                    <p>Ce site est un projet académique réalisé par des étudiants dans le cadre de leur formation. Il a pour unique objectif la démonstration de compétences techniques en développement web et ne fait l'objet d'aucune exploitation commerciale.</p>
                    <p>Projet réalisé par :</p>

                    <p>• Hassrol YA</p>
                    <p>• Adrien Pourlier</p>

                    <p>Formation : Bachelor Informatique ESGI</p>

                    <p>Année : 2025 – 2026</p>
                </article>
                <article class="mention-card">
                    <h3>
                        Hébergement
                    </h3>

                    <p>Le site est hébergé sur une infrastructure privée mise en place dans le cadre du projet pédagogique.</p>
                    <ul>
                        <li><p>Hébergement privé</p></li>
                        <li><p>Serveur auto-hébergé</p></li>
                        <li><p>Protection Cloudflare</p></li>
                        <li><p>Utilisation exclusivement académique</p></li>
                    </ul>
                </article>
                <article class="mention-card">
                    <h3>
                        Propriété intellectuelle
                    </h3>

                    <p>L'ensemble du code source, des développements spécifiques et des contenus créés pour ce projet sont la propriété de leurs auteurs, sauf indication contraire.</p>
                    <p>Certains noms, marques, logos, illustrations, visuels et éléments graphiques présents sur le site appartiennent à leurs propriétaires respectifs et demeurent protégés par les lois relatives à la propriété intellectuelle.</p>
                    <p>Ces éléments sont utilisés uniquement dans un contexte pédagogique, à des fins de démonstration, d'étude et de présentation du projet. Aucune utilisation commerciale n'est réalisée ou recherchée.</p>
                    <p>Le projet n'est ni affilié, ni approuvé, ni sponsorisé par Monster Energy Company ou par toute autre société ou marque mentionnée.</p>
                </article>
                <article class="mention-card">
                    <h3>
                        Limitation de responsabilité
                    </h3>

                    <p>Les informations présentées sur ce site sont fournies à titre démonstratif dans le cadre du projet académique.</p>
                    <p>Malgré le soin apporté à leur réalisation, aucune garantie n'est donnée quant à leur exactitude ou leur exhaustivité.</p>
                </article>
                <article class="mention-card">
                    <h3>
                        Données personnelles
                    </h3>

                    <p>Les données personnelles éventuellement collectées (compte utilisateur, adresse e-mail, pseudonyme, commentaires, etc.) sont utilisées exclusivement dans le fonctionnement du projet académique.</p>
                    <p>Elles ne sont ni revendues, ni utilisées à des fins commerciales.</p>
                    <p>Conformément à la réglementation applicable en matière de protection des données personnelles, chaque utilisateur peut demander la modification ou la suppression de ses données.</p>
                </article>
                <article class="mention-card">
                    <h3>
                        Contact
                    </h3>

                    <p>Pour toute question concernant ce projet ou pour signaler un contenu nécessitant une modification ou un retrait, vous pouvez contacter les auteurs du projet.</p>
                    <p>Toute demande légitime relative aux droits d'auteur ou à la propriété intellectuelle sera étudiée et traitée dans les meilleurs délais.</p>
                </article>
            </div>
        </main>

        <?php require_once __DIR__ . '/../components/messages.php'; ?>
        <?php require_once __DIR__ . '/../components/footer.php'; ?>
    </body>
</html>