<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['admin', 'editor']);
require_once __DIR__ . '/Configure/database.php';

$genres = $pdo->query('SELECT id, genre FROM genres ORDER BY genre')->fetchAll();

// Start with an empty game, or load the one being edited
$editId   = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$game     = ['name' => '', 'description' => '', 'price' => '0.00', 'link' => '', 'image' => null, 'banner' => null];
$selected = [];

if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM games WHERE id = ?');
    $stmt->execute([$editId]);
    $game = $stmt->fetch();
    if (!$game) {
        header('Location: Admin_Games.php');
        exit;
    }
    $stmt = $pdo->prepare('SELECT genre_id FROM game_genres WHERE game_id = ?');
    $stmt->execute([$editId]);
    $selected = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

$success = isset($_GET['saved']) ? 'Game successfully created' : '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Typed values replace the loaded ones (image and banner are kept)
    $game = array_merge($game, [
        'name'        => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price'       => trim($_POST['price'] ?? ''),
        'link'        => trim($_POST['link'] ?? ''),
    ]);
    // Keep only genre ids that exist
    $selected = array_values(array_intersect(array_map('intval', (array) ($_POST['genres'] ?? [])), array_column($genres, 'id')));

    if (!csrfOk()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($game['name'] === '' || mb_strlen($game['name']) > 55) {
        $error = 'Enter a game name of up to 55 characters.';
    } elseif (!is_numeric($game['price']) || $game['price'] < 0) {
        $error = 'The price must be a number of 0 or more.';
    } elseif ($game['link'] !== '' && !preg_match('#^(https?|steam)://\S+$#i', $game['link'])) {
        $error = 'The Play Now link must start with steam://, http:// or https://.';
    } else {
        try {
            $game['image']  = saveUpload('image', $game['image']);
            $game['banner'] = saveUpload('banner', $game['banner']);
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }
    }

    if ($error === '') {
        $values = [$game['name'], $game['description'], $game['price'], $game['link'] ?: null, $game['image'], $game['banner']];

        if ($editId) {
            $pdo->prepare('UPDATE games SET name = ?, description = ?, price = ?, link = ?, image = ?, banner = ? WHERE id = ?')
                ->execute(array_merge($values, [$editId]));
        } else {
            $pdo->prepare('INSERT INTO games (name, description, price, link, image, banner) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute($values);
            $editId = (int) $pdo->lastInsertId();
        }

        // Replace the game's genres with the ticked ones
        $pdo->prepare('DELETE FROM game_genres WHERE game_id = ?')->execute([$editId]);
        $insert = $pdo->prepare('INSERT INTO game_genres (game_id, genre_id) VALUES (?, ?)');
        foreach ($selected as $genreId) {
            $insert->execute([$editId, $genreId]);
        }

        // Reload this game's page so a refresh can't save it twice
        header('Location: Admin_Create.php?id=' . $editId . '&saved=1');
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Create game</title>
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
        <link rel="stylesheet" href="./Admin_Create.css" />
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
            <h1 class="title" id="Profile">Create Games</h1>

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

            <form class="game panel" method="post" action="Admin_Create.php<?= $editId ? '?id=' . $editId : '' ?>" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $editId ?>" />

                <label>Name</label>
                <input class="form-control" type="text" name="name" maxlength="55" value="<?= htmlspecialchars($game['name']) ?>" required />

                <label>Description</label>
                <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($game['description']) ?></textarea>

                <div class="cols">
                    <div>
                        <label>Price</label>
                        <input class="form-control" type="number" name="price" min="0" step="0.01" value="<?= htmlspecialchars($game['price']) ?>" required />
                    </div>
                    <div>
                        <label>Link</label>
                        <input class="form-control" type="text" name="link" maxlength="255" placeholder="steam://launch/... or https://..." value="<?= htmlspecialchars($game['link']) ?>" required />
                    </div>
                </div>
                <div class="genre">
                    <label>Genres</label>
                    <div class="checks">
                        <?php foreach ($genres as $genre): ?>
                        <label class="check">
                            <input type="checkbox" name="genres[]" value="<?= (int) $genre['id'] ?>" <?= in_array((int) $genre['id'], $selected, true) ? 'checked' : '' ?> />
                            <?= htmlspecialchars($genre['genre']) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                        </div>

                <div class="cols">
                    <?php foreach (['image' => 'Cover image', 'banner' => 'Banner image'] as $field => $label): ?>
                    <?php $url = gameImageUrl($game[$field]); ?>
                    <div>
                        <span class="field-label"><?= $label ?></span>
                        <label class="drop-area" for="<?= $field ?>">
                            <input type="file" accept="image/*" id="<?= $field ?>" name="<?= $field ?>" hidden />
                            <div class="img-view <?= $url ? 'has-image' : '' ?>" <?= $url ? 'style="background-image:url(\'' . htmlspecialchars($url) . '\')"' : '' ?>>
                                <?= $url ? '' : 'Click or drag and drop an image here' ?>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="actions">
                    <button type="submit" class="btn save"><?= $editId ? 'Save changes' : 'Create' ?></button>
                    <button class="btn cancel"><a href="./Admin_Create.php">Cancel</a></button>
                </div>
            </form>
        </div>

        <script src="Admin_Create.js"></script>
    </body>
</html>