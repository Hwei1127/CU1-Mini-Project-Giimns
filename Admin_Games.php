<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['admin', 'editor']);
require_once __DIR__ . '/Configure/database.php';

$success = isset($_GET['deleted']) ? 'Game deleted.' : '';
$error   = '';

// Delete a game (admins only). Its genre links, wishlist entries and reviews are removed too.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfOk()) {
        $error = 'Your session expired. Please try again.';
    } elseif (!isAdmin()) {
        $error = 'Only admins can delete games.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM games WHERE id = ?');
        $stmt->execute([(int) ($_POST['id'] ?? 0)]);
        if ($stmt->rowCount()) {
            header('Location: Admin_Games.php?deleted=1');
            exit;
        }
        $error = 'That game no longer exists.';
    }
}

$games = $pdo->query(
    "SELECT g.id, g.name, g.price, g.image,
            (SELECT GROUP_CONCAT(DISTINCT ge.genre ORDER BY ge.genre SEPARATOR ', ')
               FROM game_genres gg JOIN genres ge ON ge.id = gg.genre_id
              WHERE gg.game_id = g.id) AS genres,
            (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.game_id = g.id) AS avg_rating,
            (SELECT COUNT(*) FROM reviews r2 WHERE r2.game_id = g.id) AS review_count
       FROM games g
      ORDER BY g.name"
)->fetchAll();
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Games</title>
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous"
        />
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        />

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"
        ></script>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap"
            rel="stylesheet"
        />
        <link rel="stylesheet" href="./Admin_Games.css" />
    </head>
    <body>
 <div class="side">
    <div class="logo">
        <a href="./Store.php"
            ><img src="./Assets/Images/logo1.png"
        /></a>
    </div>
    <div class="interactions">
        <?php if (isAdmin()): ?>
        <div class="dashboard interaction">
            <a href="./Admin.php"
                ><i class="bi bi-sliders2"></i>Dashboard</a
            >
        </div>
        <?php endif; ?>
        <div class="create interaction">
            <a href="./Admin_Create.php"
                ><i class="bi bi-plus-lg"></i>Create</a
            >
        </div>
        <div class="games interaction">
            <a href="./Admin_Games.php"
                ><i class="bi bi-controller"></i>Games</a
            >
        </div>
        <?php if (isAdmin()): ?>
        <div class="reviews interaction">
            <a href="./Admin_Reviews.php"
                ><i class="bi bi-chat-left-text"></i>Reviews</a
            >
        </div>
        <div class="users interaction">
            <a href="./Admin_Users.php"
                ><i class="bi bi-people-fill"></i>Users</a
            >
        </div>
        <?php endif; ?>
    </div>
    <div class="exit">
        <a href="./Profile.php">
            <i class="bi bi-arrow-return-left"></i>Back
        </a>
        <a href="./Store.php"
            ><i class="bi bi-house-door-fill"></i>Store</a
        >
        <a href="./Sign_Out.php"
            ><i class="bi bi-box-arrow-right"></i>Sign Out</a
        >
    </div>
</div>

        <div class="main-content">
            <div class="top">
                <h1 class="title">Games</h1>
                <a class="btn new" href="./Admin_Create.php"
                    ><i class="bi bi-plus-lg"></i>New game</a
                >
            </div>

            <div class="message">
                <?php if ($success): ?>
                <div class="success-msg">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="error-msg">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="list">
                <?php if (!$games): ?>
                <p class="empty">No games yet.</p>
                <?php else: ?>
                <table>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Genres</th>
                        <th>Price</th>
                        <th>Rating</th>
                        <th></th>
                    </tr>
                    <?php foreach ($games as $g): ?>
                    <?php $img = gameImageUrl($g['image'] ?? ''); ?>
                    <tr>
                        <td>
                            <?php if ($img): ?>
                            <img class="thumb" src="<?= htmlspecialchars($img) ?>" alt="" />
                            <?php else: ?>
                            <div class="thumb empty"><i class="bi bi-controller"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($g['name']) ?></td>
                        <td><?= htmlspecialchars($g['genres'] ?? '') ?></td>
                        <td><?= (float) $g['price'] > 0 ? '$' . number_format((float) $g['price'], 2) : 'Free' ?></td>
                        <td class="rating">
                            <?= (int) $g['review_count'] ? htmlspecialchars($g['avg_rating']) . ' (' . (int) $g['review_count'] . ')' : '&ndash;' ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn edit" href="./Admin_Create.php?id=<?= (int) $g['id'] ?>"
                                    ><i class="bi bi-pencil"></i>Edit</a
                                >
                                <?php if (isAdmin()): ?>
                                <form
                                    method="post"
                                    action="Admin_Games.php"
                                    onsubmit="return confirm('Delete this game? Its reviews and wishlist entries are deleted too.');"
                                >
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int) $g['id'] ?>" />
                                    <button type="submit" class="btn delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>