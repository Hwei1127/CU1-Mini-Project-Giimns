<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['admin']);
require_once __DIR__ . '/Configure/database.php';

$success = isset($_GET['deleted']) ? 'Review deleted.' : '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfOk()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $query = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
        $query->execute([(int) ($_POST['review_id'] ?? 0)]);
        if ($query->rowCount()) {
            header('Location: Admin_Reviews.php?deleted=1');
            exit;
        }
        $error = 'That review no longer exists.';
    }
}

$reviews = $pdo->query(
    'SELECT reviews.id, reviews.rating, reviews.comment, reviews.created_at, users.name AS user_name, games.name AS game_name
       FROM reviews
       JOIN users ON users.id = reviews.user_id
       JOIN games ON games.id = reviews.game_id
      ORDER BY reviews.created_at DESC, reviews.id DESC'
)->fetchAll();
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reviews</title>
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
        <link rel="stylesheet" href="./Admin_Reviews.css" />
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
            <h1 class="title">Reviews</h1>

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
                <?php if (!$reviews): ?>
                <p class="empty">No reviews yet.</p>
                <?php else: ?>
                <table>
                    <tr>
                        <th>Game</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                    <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['game_name']) ?></td>
                        <td><?= htmlspecialchars($review['user_name']) ?></td>
                        <td class="stars">
                            <?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?>
                        </td>
                        <td><?= htmlspecialchars($review['comment'] ?? '') ?></td>
                        <td><?= htmlspecialchars(date('j M Y', strtotime($review['created_at']))) ?></td>
                        <td>
                            <form
                                method="post"
                                action="Admin_Reviews.php"
                                onsubmit="return confirm('Delete this review?');"
                            >
                                <?= csrfField() ?>
                                <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>" />
                                <button type="submit" class="btn delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>