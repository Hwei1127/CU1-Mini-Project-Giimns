<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['user', 'editor', 'admin']);
require_once __DIR__ . '/Configure/database.php';

$userId = (int) $_SESSION['user']['id'];

$success = '';
if (isset($_GET['updated'])) {
    $success = 'Review updated.';
} elseif (isset($_GET['deleted'])) {
    $success = 'Review deleted.';
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfOk()) {
        $error = 'Your session expired. Please try again.';
    } elseif (($_POST['do'] ?? '') === 'update') {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $rating   = (int) ($_POST['rating'] ?? 0);
        $comment  = trim($_POST['comment'] ?? '');

        // Only the signed-in user's own review can be changed
        $query = $pdo->prepare('SELECT id FROM reviews WHERE id = ? AND user_id = ?');
        $query->execute([$reviewId, $userId]);

        if (!$query->fetch()) {
            $error = 'That review no longer exists.';
        } elseif ($rating < 1 || $rating > 5) {
            $error = 'Please choose a rating from 1 to 5 stars.';
        } elseif (strlen($comment) > 1000) {
            $error = 'Comments can be at most 1000 characters.';
        } else {
            $query = $pdo->prepare('UPDATE reviews SET rating = ?, comment = ? WHERE id = ? AND user_id = ?');
            $query->execute([$rating, $comment, $reviewId, $userId]);
            header('Location: Reviewed.php?updated=1');
            exit;
        }
    } elseif (($_POST['do'] ?? '') === 'delete') {
        // Users can only delete their own reviews
        $query = $pdo->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?');
        $query->execute([(int) ($_POST['review_id'] ?? 0), $userId]);

        if ($query->rowCount()) {
            header('Location: Reviewed.php?deleted=1');
            exit;
        }
        $error = 'That review no longer exists.';
    }
}

$query = $pdo->prepare(
    'SELECT reviews.id, reviews.rating, reviews.comment, reviews.created_at, games.name
       FROM reviews
       JOIN games ON games.id = reviews.game_id
      WHERE reviews.user_id = ?
      ORDER BY reviews.created_at DESC'
);
$query->execute([$userId]);
$reviews = $query->fetchAll();
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reviewed</title>
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
        <link rel="stylesheet" href="Reviewed.css" />
    </head>
    <body>
        <div class="side">
            <div class="logo">
                <a href="./Store.php"
                    ><img src="./Assets/Images/logo1.png"
                /></a>
            </div>
            <div class="interactions">
                <div class="profile interaction">
                    <a href="./Profile.php"
                        ><i class="bi bi-person-fill"></i>Profile</a
                    >
                </div>
                <div class="reviews interaction">
                    <a href="./Reviewed.php"
                        ><i class="bi bi-chat-left-text"></i>Reviewed</a
                    >
                </div>
                <?php if (isAdmin()): ?>
                <div class="admin interaction">
                    <a href="./Admin.php"
                        ><i class="bi bi-shield-lock-fill"></i>Admin</a
                    >
                </div>
                <?php elseif (isEditor()): ?>
                <div class="editor interaction">
                    <a href="./Editor.php"
                        ><i class="bi bi-pencil-square"></i>Editor</a
                    >
                </div>
                <?php endif; ?>
            </div>
            <div class="exit">
                <a href="./Store.php"
                    ><i class="bi bi-house-door-fill"></i>Store</a
                >
                <a href="./Sign_Out.php"
                    ><i class="bi bi-box-arrow-right"></i>Sign Out</a
                >
            </div>
        </div>

        <div class="main-content">
            <h1 class="title" id="Reviewed">My Reviews</h1>

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

            <?php if (!$reviews): ?>
            <p class="empty">
                You haven't reviewed any games yet. Open a game in the
                <a href="./Store.php">store</a> to leave a review.
            </p>
            <?php endif; ?>

            <div class="settings">
                <?php foreach ($reviews as $review): ?>
                <div class="review panel">
                    <h3>
                        <?= htmlspecialchars($review['name']) ?>
                        <small><?= htmlspecialchars(date('j M Y', strtotime($review['created_at']))) ?></small>
                    </h3>

                    <form method="post" action="Reviewed.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="do" value="update" />
                        <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>" />

                        <label>Rating</label>
                        <fieldset class="rating">
                            <?php for ($star = 5; $star >= 1; $star--): ?>
                            <input
                                type="radio"
                                id="star<?= $star ?>-<?= (int) $review['id'] ?>"
                                name="rating"
                                value="<?= $star ?>"
                                <?= (int) $review['rating'] === $star ? 'checked' : '' ?>
                            /><label
                                for="star<?= $star ?>-<?= (int) $review['id'] ?>"
                                title="<?= $star ?> star<?= $star > 1 ? 's' : '' ?>"
                                >&#9733;</label
                            >
                            <?php endfor; ?>
                        </fieldset>

                        <label>Comment</label>
                        <textarea class="form-control" name="comment" rows="3" maxlength="1000" placeholder="Comment (optional)"><?= htmlspecialchars($review['comment'] ?? '') ?></textarea>
                         <div class="button">
                        <button type="submit" class="btn submit">Update</button>
                    </form>                              
                    <form
                        method="post"
                        action="Reviewed.php"
                        onsubmit="return confirm('Delete this review?');"
                    >
                        <?= csrfField() ?>
                        <input type="hidden" name="do" value="delete" />
                        <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>" />
                        <button type="submit" class="btn delete">Delete</button>
                    </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
</html>