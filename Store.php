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

    $query = $pdo->prepare('SELECT id FROM games WHERE id = ?');
    $query->execute([$gameId]);
    $gameFound = $query->fetch();

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
            $query = $pdo->prepare(
                'INSERT INTO reviews (user_id, game_id, rating, comment) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)'
            );
            $query->execute([$userId, $gameId, $rating, $comment]);
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
        $query = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND game_id = ?');
        $query->execute([$userId, $gameId]);

        if (!$query->rowCount()) {
            $query = $pdo->prepare('INSERT IGNORE INTO wishlist (user_id, game_id) VALUES (?, ?)');
            $query->execute([$userId, $gameId]);
        }
        header('Location: Store.php?open=' . $gameId);
        exit;
    }
}

// All games + their genres + average rating + whether this user wishlisted it
$query = $pdo->prepare("
    SELECT games.id, games.name, games.image, games.banner, games.link, games.description, games.price,
           GROUP_CONCAT(DISTINCT genres.genre ORDER BY genres.genre SEPARATOR ',') AS genres,
           (SELECT ROUND(AVG(reviews.rating), 1) FROM reviews WHERE reviews.game_id = games.id) AS average_rating,
           (SELECT COUNT(*) FROM wishlist WHERE wishlist.game_id = games.id AND wishlist.user_id = :userId) AS wished
    FROM games
    LEFT JOIN game_genres ON game_genres.game_id = games.id
    LEFT JOIN genres ON genres.id = game_genres.genre_id
    GROUP BY games.id
    ORDER BY games.created_at DESC, games.id DESC
");
$query->execute(['userId' => $userId]);
$games = $query->fetchAll();

// Every review, grouped by game
$reviewsByGame = [];
$reviewRows = $pdo->query(
    'SELECT reviews.id, reviews.game_id, reviews.user_id, reviews.rating, reviews.comment, reviews.created_at, users.name AS user_name
       FROM reviews
       JOIN users ON users.id = reviews.user_id
      ORDER BY reviews.created_at DESC, reviews.id DESC'
)->fetchAll();
foreach ($reviewRows as $reviewRow) {
    $reviewsByGame[$reviewRow['game_id']][] = $reviewRow;
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
                    <input type="search" placeholder="Search" id="searchInput" oninput="applyFilters()"/>
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
            <h2 class="section-title">Explore by Genre</h2>
            <div class="genre-filter" id="genreFilter">
                <button class="chip active" data-genre="">All</button>
                <?php foreach ($allGenres as $genre): ?>
                <button class="chip" data-genre="<?= htmlspecialchars(strtolower($genre)) ?>">
                    <?= htmlspecialchars($genre) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <h2 class="section-title">All Games <small>(<span id="gameCount"><?= count($games) ?></span>)</small></h2>

            <ul id="gameGrid" class="store-grid">
                <?php foreach ($games as $game): ?>
                <?php
                $gameId         = (int) $game['id'];
                $coverUrl    = gameImageUrl($game['image']) ?: gameImageUrl($game['banner']);
                $bannerUrl   = gameImageUrl($game['banner']) ?: gameImageUrl($game['image']);
                $genreList   = $game['genres'] ? explode(',', $game['genres']) : [];
                $gameReviews = $reviewsByGame[$game['id']] ?? [];
                $isWishlisted      = (int) $game['wished'] > 0;
                $reviewCount = count($gameReviews);
                $averagePercent  = $game['average_rating'] !== null ? (float) $game['average_rating'] / 5 * 100 : 0;

                // This user's own review of the game (if there is one)
                $userReview = null;
                foreach ($gameReviews as $review) {
                    if ((int) $review['user_id'] === $userId) {
                        $userReview = $review;
                        break;
                    }
                }
                $userRating  = $userReview ? (int) $userReview['rating'] : 0;
                $userComment = $userReview['comment'] ?? '';
                ?>
                <li
                    class="game-tile"
                    data-name="<?= htmlspecialchars(strtolower($game['name'])) ?>"
                    data-genres="<?= htmlspecialchars(strtolower($game['genres'] ?? '')) ?>"
                >
                    <button class="open-btn" popovertarget="card-<?= $gameId ?>" aria-label="Open <?= htmlspecialchars($game['name']) ?>">
                        <?php if ($coverUrl): ?>
                        <img src="<?= htmlspecialchars($coverUrl) ?>" alt="" />
                        <?php else: ?>
                        <span class="tile-empty"><i class="bi bi-controller"></i></span>
                        <?php endif; ?>
                    </button>

                    <div class="pop" id="card-<?= $gameId ?>" popover>
                        <div class="card">
                            <div class="image">
                                <?php if ($bannerUrl): ?>
                                <img src="<?= htmlspecialchars($bannerUrl) ?>" alt="<?= htmlspecialchars($game['name']) ?> banner" />
                                <?php endif; ?>
                            </div>
                            <div class="info">
                                <div class="about">
                                    <button
                                        class="close-btn"
                                        popovertarget="card-<?= $gameId ?>"
                                        popovertargetaction="hide"
                                        aria-label="Close"
                                    >
                                        ✕
                                    </button>

                                    <?php if ($openId === $gameId && ($success || $error)): ?>
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
                                            <h2><?= htmlspecialchars($game['name']) ?></h2>
                                            <form class="wishlist-form" method="post" action="Store.php">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="do" value="wishlist" />
                                                <input type="hidden" name="game_id" value="<?= $gameId ?>" />
                                                <button
                                                    type="submit"
                                                    class="wishlist <?= $isWishlisted ? 'on' : '' ?>"
                                                    aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
                                                    aria-label="Add to wishlist"
                                                >
                                                    <i class="bi <?= $isWishlisted ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <p class="genre">
                                            <?= implode(' · ', array_map('htmlspecialchars', $genreList)) ?>
                                        </p>
                                        <p class="meta">
                                            <?= $game['price'] > 0 ? '$' . number_format($game['price'], 2) : 'Free' ?>
                                        </p>
                                    </div>

                                    <div class="description">
                                        <p class="desc"><?= htmlspecialchars($game['description'] ?? '') ?></p>
                                        <div class="interaction">
                                            <div class="rating-form">
                                                <h4>Rating</h4>
                                                <div class="average">
                                                    <span class="avg-stars" style="--fill: <?= $averagePercent ?>%">★★★★★</span>
                                                    <span class="avg-text">
                                                        <?php if ($reviewCount): ?>
                                                        <?= htmlspecialchars($game['average_rating']) ?> (<?= $reviewCount ?>)
                                                        <?php else: ?>
                                                        No ratings yet
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if (!empty($game['link'])): ?>
                                            <a class="click" href="<?= htmlspecialchars($game['link']) ?>"
                                                >Play Now<i class="bi bi-box-arrow-up-right"></i
                                            ></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="comment">
                                        <?php if (!$gameReviews): ?>
                                        <p class="no-comments">No reviews yet. Be the first to review this game.</p>
                                        <?php endif; ?>
                                        <?php foreach ($gameReviews as $review): ?>
                                        <?php $isMine = ((int) $review['user_id'] === $userId); ?>
                                        <div class="comment-item">
                                            <div class="who">
                                                <strong>
                                                    <?= htmlspecialchars($review['user_name']) ?>
                                                    <?php if ($isMine): ?><small>(you)</small><?php endif; ?>
                                                </strong>
                                                <span class="stars-ro">
                                                    <?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?>
                                                </span>
                                                <?php if ($isMine || isAdmin()): ?>
                                                <form
                                                    class="del"
                                                    method="post"
                                                    action="Store.php"
                                                    onsubmit="return confirm('Delete this review?');"
                                                >
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="do" value="delete" />
                                                    <input type="hidden" name="game_id" value="<?= $gameId ?>" />
                                                    <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>" />
                                                    <button type="submit" aria-label="Delete review">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (trim((string) $review['comment']) !== ''): ?>
                                            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <form class="review" method="post" action="Store.php">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="do" value="review" />
                                        <input type="hidden" name="game_id" value="<?= $gameId ?>" />
                                        <fieldset class="rating ratings">
                                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                            <input
                                                type="radio"
                                                id="r-star<?= $star ?>-<?= $gameId ?>"
                                                name="rating"
                                                value="<?= $star ?>"
                                                <?= $userRating === $star ? 'checked' : '' ?>
                                            /><label
                                                for="r-star<?= $star ?>-<?= $gameId ?>"
                                                title="<?= $star ?> star<?= $star > 1 ? 's' : '' ?>"
                                                >&#9733;</label
                                            >
                                            <?php endfor; ?>
                                        </fieldset>
                                        <div class="add-comment">
                                            <input
                                                name="comment"
                                                value="<?= htmlspecialchars($userComment) ?>"
                                                placeholder="Write a review..."
                                                autocomplete="off"
                                                maxlength="1000"
                                                required
                                            />
                                            <button type="submit" class="btn-submit">
                                                <?= $userReview ? 'Update' : 'Post' ?>
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