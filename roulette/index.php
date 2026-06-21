<?php
require_once __DIR__ . '/../utils/session.php';

$mode = $_GET['mode'] ?? 'all';
$userId = $_SESSION['user']['id'] ?? 0;

if ($mode === 'never_tried' && $userId) {
    $stmt = $pdo->prepare("
        SELECT m.id_monsters, m.nom, m.image
        FROM monsters m
        WHERE m.id_monsters NOT IN (
            SELECT id_monsters
            FROM monster_drinks
            WHERE id_users = ?
        )
    ");
    $stmt->execute([$userId]);
    $allMonsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($mode === 'favorites' && $userId) {
    $stmt = $pdo->prepare("
        SELECT m.id_monsters, m.nom, m.image
        FROM monsters m
        INNER JOIN monster_favorites f
            ON f.id_monsters = m.id_monsters
        WHERE f.id_users = ?
    ");
    $stmt->execute([$userId]);
    $allMonsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    $allMonsters = $pdo->query("
        SELECT id_monsters, nom, image
        FROM monsters
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Monster | Roulette</title>

        <link rel="shortcut icon" href="/favicon.png" type="image/png">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="/roulette/styles.css">
        <link rel="stylesheet" href="/styles/home.css">
    </head>

    <body>

    <header>
        <?php require_once __DIR__ . '/../components/navbar.php'; ?>
    </header>

    <?php require_once __DIR__ . '/../components/alert.php'; ?>

    <main class="roulette-page">

        <div class="roulette-card">

            <h1>Roulette Monster</h1>

            <p>
                Pas d'idée ?
                Laisse la roulette choisir pour toi.
            </p>

            <div class="roulette-container">

                <div id="roulette-slot">
                    <span class="roulette-placeholder">
                        🎲
                    </span>
                </div>

                <button id="spinBtn" class="roulette-btn">
                    Lancer la roulette
                </button>

                <select name="mode" id="select-roulette">
                    <option value="all" <?= $mode === 'all' ? 'selected' : '' ?>>
                    Toutes les Monsters
                    </option>

                    <option value="never_tried" <?= $mode === 'never_tried' ? 'selected' : '' ?>>
                    Monsters jamais essayées
                    </option>

                    <option value="favorites" <?= $mode === 'favorites' ? 'selected' : '' ?>>
                    Mes favorites
                    </option>
                </select>
            </div>

        </div>

    </main>

    <div id="roulette-popup" class="roulette-popup">

        <div class="roulette-popup-card">

            <button id="popup-close" class="popup-close">
                ×
            </button>

            <h2>Monster choisie</h2>

            <img
                id="popup-img"
                src=""
                alt="Monster"
            >

            <h3 id="popup-name"></h3>

        </div>

    </div>

    <script>const monsters = <?= json_encode($allMonsters) ?>;</script>
    <script src="/roulette/app.js"></script>
    <?php require_once __DIR__ . '/../components/messages.php'; ?>
    </body>
</html>