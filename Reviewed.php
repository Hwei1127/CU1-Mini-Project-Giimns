<?php
require_once __DIR__ . '/Auth/role.php';
requireRole(['user', 'editor', 'admin']);
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
                <div class="profile interaction active">
                    <a href="./Profile.php"
                        ><i class="bi bi-person-fill"></i>Profile</a
                    >
                </div>
                <div class="reviews interaction">
                    <a href="./Reviewed.php"
                        ><i class="bi bi-chat-left-text"></i></i>Reviewed</a
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
            <a href="./Editor.php"><i class="bi bi-pencil-square"></i>Editor</a>
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
        <div class="main-content"></div>
    </body>
</html>
