<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['user', 'editor', 'admin']);
require_once __DIR__ . '/Configure/database.php';

$userId  = (int) $_SESSION['user']['id'];
$success = '';
$error   = '';
$openId  = (int) ($_GET['open'] ?? 0);

if (isset($_GET['saved'])) {
    $success = 'Review saved.';
} elseif (isset($_GET['deleted'])) {
    $success = 'Review deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gameId = (int) ($_POST['game_id'] ?? 0);
    $openId = $gameId; // this game's card opens again when the page loads

    $stmt = $pdo->prepare('SELECT id FROM games WHERE id = ?');
    $stmt->execute([$gameId]);
    $gameFound = $stmt->fetch();

    if (!csrfOk()) {
        $error = 'Your session expired. Please try again.';
    } elseif (!$gameFound) {
        $error = 'That game no longer exists.';
    } elseif (($_POST['do'] ?? '') === 'review') {
        $rating  = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $error = 'Please choose a rating from 1 to 5 stars.';
        } elseif (strlen($comment) > 1000) {
            $error = 'Comments can be at most 1000 characters.';
        } else {
            // One review per user and game: add it, or update the one that exists
            $stmt = $pdo->prepare(
                'INSERT INTO reviews (user_id, game_id, rating, comment) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)'
            );
            $stmt->execute([$userId, $gameId, $rating, $comment]);
            header('Location: Store.php?open=' . $gameId . '&saved=1');
            exit;
        }
    } elseif (($_POST['do'] ?? '') === 'delete') {
        // Users can delete their own review, admins can delete any review
        $result = deleteReview($pdo, (int) ($_POST['review_id'] ?? 0), $userId, isAdmin());

        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            header('Location: Store.php?open=' . $gameId . '&deleted=1');
            exit;
        }
    } elseif (($_POST['do'] ?? '') === 'wishlist') {
        // Take the game off the wishlist, or add it when it is not there yet
        $stmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND game_id = ?');
        $stmt->execute([$userId, $gameId]);

        if (!$stmt->rowCount()) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO wishlist (user_id, game_id) VALUES (?, ?)');
            $stmt->execute([$userId, $gameId]);
        }
        header('Location: Store.php?open=' . $gameId);
        exit;
    }
}

// All games + their genres + average rating + whether this user wishlisted it
$stmt = $pdo->prepare("
    SELECT g.id, g.name, g.image, g.banner, g.link, g.description, g.price,
           GROUP_CONCAT(DISTINCT ge.genre ORDER BY ge.genre SEPARATOR ',') AS genres,
           (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.game_id = g.id) AS avg_rating,
           (SELECT COUNT(*) FROM wishlist w WHERE w.game_id = g.id AND w.user_id = :uid) AS wished
    FROM games g
    LEFT JOIN game_genres gg ON gg.game_id = g.id
    LEFT JOIN genres ge ON ge.id = gg.genre_id
    GROUP BY g.id
    ORDER BY g.created_at DESC, g.id DESC
");
$stmt->execute(['uid' => $userId]);
$games = $stmt->fetchAll();

// Every review, grouped by game
$reviewsByGame = [];
$rows = $pdo->query(
    'SELECT r.id, r.game_id, r.user_id, r.rating, r.comment, r.created_at, u.name AS user_name
       FROM reviews r
       JOIN users u ON u.id = r.user_id
      ORDER BY r.created_at DESC, r.id DESC'
)->fetchAll();
foreach ($rows as $row) {
    $reviewsByGame[$row['game_id']][] = $row;
}

$allGenres = $pdo->query('SELECT genre FROM genres ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Store</title>
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
        <link rel="stylesheet" href="Store.css" />
    </head>
    <body>
        <div class="nav-bar">
            <div class="link">
                <div class="logo">
                    <a href="./Store.php"
                        ><img src="Assets/Images/logo1.png" alt="Giimns"
                    /></a>
                </div>
                <div class="pages">
                    <div class="store">
                        <a href="./Store.php">STORE</a>
                    </div>

                    <a href="./Library.php">LIBRARY</a>
                </div>
            </div>
            <div class="right_nav">
               <div class="search">
                    <i class="bi bi-search"></i>
                    <input type="search" placeholder="Search" id="myInput" onkeyup="myFunction()"/>
                </div>
                <div class="profile">
                <a  href="./Profile.php"><i class="bi bi-person-fill"></i></a>
                </div>
                
                <div class="signout">
                    <a href="Sign_Out.php"
                        ><i class="bi bi-box-arrow-right"></i
                    ></a>
                </div>
            </div>
        </div>

        <div class="banner"></div>

        <main class="store-wrap">
            <div class="genre-filter" id="genreFilter">
                <button class="chip active" data-genre="">All</button>
                <?php foreach ($allGenres as $genre): ?>
                <button class="chip" data-genre="<?= htmlspecialchars(strtolower($genre)) ?>">
                    <?= htmlspecialchars($genre) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <ul id="myUL" class="store-grid">
                <?php foreach ($games as $g): ?>
                <?php
                $gid         = (int) $g['id'];
                $coverUrl    = gameImageUrl($g['image']) ?: gameImageUrl($g['banner']);
                $bannerUrl   = gameImageUrl($g['banner']) ?: gameImageUrl($g['image']);
                $genreList   = $g['genres'] ? explode(',', $g['genres']) : [];
                $gameReviews = $reviewsByGame[$g['id']] ?? [];
                $wished      = (int) $g['wished'] > 0;
                $reviewCount = count($gameReviews);
                $avgPercent  = $g['avg_rating'] !== null ? (float) $g['avg_rating'] / 5 * 100 : 0;

                // This user's own review of the game (if there is one)
                $myReview = null;
                foreach ($gameReviews as $rv) {
                    if ((int) $rv['user_id'] === $userId) {
                        $myReview = $rv;
                        break;
                    }
                }
                $myRating  = $myReview ? (int) $myReview['rating'] : 0;
                $myComment = $myReview['comment'] ?? '';
                ?>
                <li
                    class="game-tile"
                    data-name="<?= htmlspecialchars(strtolower($g['name'])) ?>"
                    data-genres="<?= htmlspecialchars(strtolower($g['genres'] ?? '')) ?>"
                >
                    <button class="open-btn" popovertarget="card-<?= $gid ?>" aria-label="Open <?= htmlspecialchars($g['name']) ?>">
                        <?php if ($coverUrl): ?>
                        <img src="<?= htmlspecialchars($coverUrl) ?>" alt="" />
                        <?php else: ?>
                        <span class="tile-empty"><i class="bi bi-controller"></i></span>
                        <?php endif; ?>
                    </button>

                    <div class="pop" id="card-<?= $gid ?>" popover>
                        <div class="card">
                            <div class="image">
                                <?php if ($bannerUrl): ?>
                                <img src="<?= htmlspecialchars($bannerUrl) ?>" alt="<?= htmlspecialchars($g['name']) ?> banner" />
                                <?php endif; ?>
                            </div>
                            <div class="info">
                                <div class="about">
                                    <button
                                        class="close-btn"
                                        popovertarget="card-<?= $gid ?>"
                                        popovertargetaction="hide"
                                        aria-label="Close"
                                    >
                                        ✕
                                    </button>

                                    <?php if ($openId === $gid && ($success || $error)): ?>
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
                                    <?php endif; ?>

                                    <div class="title">
                                        <div class="section">
                                            <h2><?= htmlspecialchars($g['name']) ?></h2>
                                            <form class="wishlist-form" method="post" action="Store.php">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="do" value="wishlist" />
                                                <input type="hidden" name="game_id" value="<?= $gid ?>" />
                                                <button
                                                    type="submit"
                                                    class="wishlist <?= $wished ? 'on' : '' ?>"
                                                    aria-pressed="<?= $wished ? 'true' : 'false' ?>"
                                                    aria-label="Add to wishlist"
                                                >
                                                    <i class="bi <?= $wished ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <p class="genre">
                                            <?= implode(' · ', array_map('htmlspecialchars', $genreList)) ?>
                                        </p>
                                        <p class="meta">
                                            <?= $g['price'] > 0 ? '$' . number_format($g['price'], 2) : 'Free' ?>
                                        </p>
                                    </div>

                                    <div class="description">
                                        <p class="desc"><?= htmlspecialchars($g['description'] ?? '') ?></p>
                                        <div class="interaction">
                                            <div class="rating-form">
                                                <h4>Rating</h4>
                                                <div class="average">
                                                    <span class="avg-stars" style="--fill: <?= $avgPercent ?>%">★★★★★</span>
                                                    <span class="avg-text">
                                                        <?php if ($reviewCount): ?>
                                                        <?= htmlspecialchars($g['avg_rating']) ?> (<?= $reviewCount ?>)
                                                        <?php else: ?>
                                                        No ratings yet
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if (!empty($g['link'])): ?>
                                            <a class="click" href="<?= htmlspecialchars($g['link']) ?>"
                                                >Play Now<i class="bi bi-box-arrow-up-right"></i
                                            ></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="comment">
                                        <?php if (!$gameReviews): ?>
                                        <p class="no-comments">No reviews yet. Be the first to review this game.</p>
                                        <?php endif; ?>
                                        <?php foreach ($gameReviews as $rv): ?>
                                        <?php $mine = ((int) $rv['user_id'] === $userId); ?>
                                        <div class="comment-item">
                                            <div class="who">
                                                <strong>
                                                    <?= htmlspecialchars($rv['user_name']) ?>
                                                    <?php if ($mine): ?><small>(you)</small><?php endif; ?>
                                                </strong>
                                                <span class="stars-ro">
                                                    <?= str_repeat('★', (int) $rv['rating']) . str_repeat('☆', 5 - (int) $rv['rating']) ?>
                                                </span>
                                                <?php if ($mine || isAdmin()): ?>
                                                <form
                                                    class="del"
                                                    method="post"
                                                    action="Store.php"
                                                    onsubmit="return confirm('Delete this review?');"
                                                >
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="do" value="delete" />
                                                    <input type="hidden" name="game_id" value="<?= $gid ?>" />
                                                    <input type="hidden" name="review_id" value="<?= (int) $rv['id'] ?>" />
                                                    <button type="submit" aria-label="Delete review">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (trim((string) $rv['comment']) !== ''): ?>
                                            <p><?= nl2br(htmlspecialchars($rv['comment'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <form class="review" method="post" action="Store.php">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="do" value="review" />
                                        <input type="hidden" name="game_id" value="<?= $gid ?>" />
                                        <fieldset class="rating ratings">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input
                                                type="radio"
                                                id="r-star<?= $i ?>-<?= $gid ?>"
                                                name="rating"
                                                value="<?= $i ?>"
                                                <?= $myRating === $i ? 'checked' : '' ?>
                                            /><label
                                                for="r-star<?= $i ?>-<?= $gid ?>"
                                                title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>"
                                                >&#9733;</label
                                            >
                                            <?php endfor; ?>
                                        </fieldset>
                                        <div class="add-comment">
                                            <input
                                                name="comment"
                                                value="<?= htmlspecialchars($myComment) ?>"
                                                placeholder="Write a review..."
                                                autocomplete="off"
                                                maxlength="1000"
                                                required
                                            />
                                            <button type="submit" class="btn-submit">
                                                <?= $myReview ? 'Update' : 'Post' ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <p id="noResults" class="no-results" hidden>No games match your search.</p>
        </main>

        <script src="Store.js"></script>
    </body>
</html>