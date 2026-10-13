<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['user', 'editor', 'admin']);
require_once __DIR__ . '/Configure/database.php';

$userId  = $_SESSION['user']['id'];
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['do'] ?? '') === 'name') {

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $error = 'Username cannot be empty.';
        } else {
            $query = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
            $query->execute([$name, $userId]);

            $_SESSION['user']['name'] = $name;
            $success = 'Username updated.';
        }

    } elseif (($_POST['do'] ?? '') === 'password') {

        $query = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $query->execute([$userId]);
        $user = $query->fetch();

        if (!password_verify($_POST['current_password'] ?? '', $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($_POST['new_password'] ?? '') < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'New passwords do not match.';
        } else {
            $query = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $query->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $userId]);

            $success = 'Password updated.';
        }
    }
}

$query = $pdo->prepare('SELECT created_at FROM users WHERE id = ?');
$query->execute([$userId]);
$createdAt = $query->fetchColumn();

$stats = ['wishes' => 0, 'reviews' => 0];

$query = $pdo->prepare('SELECT COUNT(DISTINCT game_id) FROM wishlist WHERE user_id = ?');
$query->execute([$userId]);
$stats['wishes'] = (int) $query->fetchColumn();

$query = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$query->execute([$userId]);
$stats['reviews'] = (int) $query->fetchColumn();

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=, initial-scale=1.0" />
        <title>Profile</title>
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
        <link rel="stylesheet" href="Profile.css" />
    </head>
    <body>
        <div class="Profile">
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
                        <a href="./Admin_Create.php"
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
                <h1 class="title" id="Profile">Profile</h1>

                <div class="top">
                    <div class="user">
                        <div class="avatar">
                            <h2>
                                <strong
                                    ><?= htmlspecialchars($_SESSION['user']['name'] ?? 'none') ?></strong
                                >
                            </h2>
                            <p>
                                <?= htmlspecialchars($_SESSION['user']['role']
                                ?? 'none') ?>
                            </p>
                        </div>

                        <div class="info">
                            <p>
                                <?= htmlspecialchars($_SESSION['user']['email']
                                ?? 'Guest') ?>
                                <strong>&middot;</strong> Member since <?=
                                $createdAt ? htmlspecialchars(date('j M Y',
                                strtotime($createdAt))) : 'unknown' ?>
                            </p>
                            <div class="status">
                                <p>
                                    <i class="bi bi-bookmark"></i> <?= (int)
                                    $stats['wishes'] ?> wishlist
                                </p>
                                <p>
                                    <i class="bi bi-chat-left-text"></i> <?=
                                    (int) $stats['reviews'] ?> reviews
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bottom">
                    <div class="message">
                        <?php if ($success): ?>
                        <div class="success-msg">
                            <?= htmlspecialchars($success) ?>
                        </div>
                        <?php endif; ?> <?php if ($error): ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                
                    <div class="settings">
                        <form class="username panel" method="post" action="Profile.php">
    <h3>Change username</h3>
    <input type="hidden" name="do" value="name" />
    <label>Username</label>
    <input
        class="form-control"
        type="text"
        name="name"
        value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>"
        maxlength="255"
        required
    />
    <button type="submit" class="btn">Save</button>
</form>

<form class="new_password panel" method="post" action="Profile.php">
    <h3>Change password</h3>
    <input type="hidden" name="do" value="password" />
    <label>Current password</label>
    <input class="form-control" type="password" name="current_password" required />
    <label>New password (8+ characters)</label>
    <input class="form-control" type="password" name="new_password" minlength="8" required />
    <label>Confirm new password</label>
    <input class="form-control" type="password" name="confirm_password" minlength="8" required />
    <button type="submit" class="btn">Update password</button>
</form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>