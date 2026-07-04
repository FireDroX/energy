<?php

function countQuery(PDO $pdo, string $sql, array $params): int {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function getEasterEgg(PDO $pdo, int $id_users, string $nom): bool{
    $stmt = $pdo->prepare("SELECT id_egg FROM easter_eggs WHERE nom = :nom LIMIT 1");
    $stmt->execute(['nom' => $nom]);
    $egg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$egg) {
        return false;
    }

    $id_egg = (int) $egg['id_egg'];

    $stmt = $pdo->prepare("SELECT 1 FROM user_eggs WHERE id_users = :id_users AND id_egg = :id_egg");
    $stmt->execute(['id_users' => $id_users, 'id_egg' => $id_egg]);
    if ($stmt->fetch()) {
        return true;
    }

    $unlocked = false;

    switch ($nom) {
        case 'liked_10':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM monster_favorites WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 10;
            break;

        case 'rate_10':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM notes WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 10;
            break;

        case 'rate_20':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM notes WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 20;
            break;

        case 'lot_of_notifications':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM messages WHERE receiver_id = :id AND lu = 0",
                ['id' => $id_users]
            );
            $unlocked = $count > 9;
            break;

        case 'looked_at_all':
            $viewed = countQuery(
                $pdo,
                "SELECT COUNT(DISTINCT id_monsters) FROM monster_views WHERE id_users = :id",
                ['id' => $id_users]
            );
            $total = countQuery($pdo, "SELECT COUNT(*) FROM monsters", []);
            $unlocked = $total > 0 && $viewed >= $total;
            break;

        case 'im_a_teapot':
            $unlocked = true;
            break;

        case 'midnight':
            $unlocked = date('H:i') === '00:00';
            break;

        case 'answered_20':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM commentaires WHERE id_users = :id AND id_parent IS NOT NULL",
                ['id' => $id_users]
            );
            $unlocked = $count >= 20;
            break;

        case 'vip':
            $unlocked = true;
            break;

        case 'drank_10':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM monster_drinks WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 10;
            break;

        case 'drank_50':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM monster_drinks WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 50;
            break;

        case 'drank_3_sameday':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM monster_drinks WHERE id_users = :id AND DATE(date_drink) = CURDATE()",
                ['id' => $id_users]
            );
            $unlocked = $count >= 3;
            break;

        case 'view_100':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM monster_views WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 100;
            break;

        case 'liked_50_comments':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM likes WHERE id_users = :id",
                ['id' => $id_users]
            );
            $unlocked = $count >= 50;
            break;

        case 'write_10_comments':
            $count = countQuery(
                $pdo,
                "SELECT COUNT(*) FROM commentaires WHERE id_users = :id AND id_parent IS NULL",
                ['id' => $id_users]
            );
            $unlocked = $count >= 10;
            break;

        case 'roulette_liked':
            $unlocked = true;
            break;

        case 'roulette_not_drank':
            $unlocked = true;
            break;

        case 'roulette_all':
            $unlocked = true;
            break;

        default:
            return false;
    }

    if ($unlocked) {
        $stmt = $pdo->prepare(
            "INSERT INTO user_eggs (id_users, id_egg) VALUES (:id_users, :id_egg)"
        );
        $stmt->execute(['id_users' => $id_users, 'id_egg' => $id_egg]);
        return true;
    }

    return false;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once __DIR__ . '/../../utils/session.php';
    require_once __DIR__ . '/../../utils/database.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => true, 'message' => 'no_access']);
        exit;
    }

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    $nom = $data['nom'] ?? $_POST['nom'] ?? null;

    if (!$nom) {
        http_response_code(400);
        echo json_encode(['warning' => true, 'message' => 'missing_fields']);
        exit;
    }

    $id_users = (int) $_SESSION['user']['id'];

    $unlocked = getEasterEgg($pdo, $id_users, $nom);

    echo json_encode([
        'success'  => true,
        'unlocked' => $unlocked,
    ]);
    exit;
}