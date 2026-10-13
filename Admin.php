<?php
require_once __DIR__ . '/Auth/role.php';
if (!isAdmin()) {
    header('Location: No-access.php');
    exit;
}
require_once __DIR__ . '/Configure/database.php';

$totals = $pdo->query(
    'SELECT (SELECT COUNT(*) FROM games)    AS games,
            (SELECT COUNT(*) FROM users)    AS users,
            (SELECT COUNT(*) FROM reviews)  AS reviews,
            (SELECT COUNT(*) FROM wishlist) AS wishes'
)->fetch();
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin</title>
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
        <link rel="stylesheet" href="./Admin.css" />
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
            <h1 class="title">Admin dashboard</h1>

            <div class="stats">
                <div class="stat">
                    <a href="./Admin_Games.php">
                    <h2><?= (int) $totals['games'] ?></h2>
                    <p>Games</p>
</a>
                </div>
                <div class="stat">
                    <a href="./Admin_Reviews.php">
                    <h2><?= (int) $totals['reviews'] ?></h2>
                    <p>Reviews</p>
                                        </a>
                </div>

                <div class="stat">
                                        <a href="./Admin_Users.php">
                    <h2><?= (int) $totals['users'] ?></h2>
                    <p>Users</p>
                    </a>
                </div>
                <div class="stat">
                    <h2><?= (int) $totals['wishes'] ?></h2>
                    <p>Wishlist entries</p>
                </div>
            </div>
        </div>
    </body>
</html>