<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['admin']);
require_once __DIR__ . '/Configure/database.php';

$roles = ['user', 'editor', 'admin'];
$currentUserId  = (int) $_SESSION['user']['id'];

$success = '';
if (isset($_GET['updated'])) {
    $success = 'Role updated. It applies the next time that user signs in.';
} elseif (isset($_GET['deleted'])) {
    $success = 'User deleted.';
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = (int) ($_POST['id'] ?? 0);

    if (!csrfOk()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($targetId === $currentUserId) {
        $error = 'You cannot change or delete your own account here.';
    } elseif (($_POST['do'] ?? '') === 'role') {
        $role = $_POST['role'] ?? '';
        if (in_array($role, $roles, true)) {
            $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $targetId]);
            header('Location: Admin_Users.php?updated=1');
            exit;
        }
        $error = 'Invalid role.';
    } elseif (($_POST['do'] ?? '') === 'delete') {
        // Their wishlist entries and reviews are deleted with them
        $query = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $query->execute([$targetId]);
        if ($query->rowCount()) {
            header('Location: Admin_Users.php?deleted=1');
            exit;
        }
        $error = 'That user no longer exists.';
    }
}

$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at, id')->fetchAll();
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Users</title>
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
        <link rel="stylesheet" href="./Admin_Users.css" />
    </head>
    <body>
        <div class="side">
            <div class="logo">
                <a href="./Store.php"
                    ><img src="./Assets/Images/logo1.png"
                /></a>
            </div>
            <div class="interactions">
                <div class="dashboard interaction">
                    <a href="./Admin.php"
                        ><i class="bi bi-sliders2"></i>Dashboard</a
                    >
                </div>
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
            <h1 class="title">Users</h1>

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
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                    <?php foreach ($users as $user): ?>
                    <?php $isCurrentUser = ((int) $user['id'] === $currentUserId); ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($user['name']) ?>
                            <?php if ($isCurrentUser): ?><small>(you)</small><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($isCurrentUser): ?>
                            <span class="role <?= htmlspecialchars($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
                            <?php else: ?>
                            <form class="change" method="post" action="Admin_Users.php">
                                <?= csrfField() ?>
                                <input type="hidden" name="do" value="role" />
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>" />
                                <select name="role">
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?= htmlspecialchars($role) ?>" <?= $user['role'] === $role ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn save">Save</button>
                            </form>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('j M Y', strtotime($user['created_at']))) ?></td>
                        <td>
                            <?php if (!$isCurrentUser): ?>
                            <form
                                method="post"
                                action="Admin_Users.php"
                                onsubmit="return confirm('Delete this user and all their reviews and wishlist entries?');"
                            >
                                <?= csrfField() ?>
                                <input type="hidden" name="do" value="delete" />
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>" />
                                <button type="submit" class="btn delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </body>
</html>