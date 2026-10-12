<?php
require_once __DIR__ . '/Auth/role.php';
if (!isEditor()) {
    header('Location: No-access.php');
    exit;
}

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
            </div>
            <div class="exit">
                <a href="./Profile.php">
                    <i class="bi bi-arrow-return-left"></i>Back
                </a>
                <a href="./Store.php"
                    ><i class="bi bi-house-door-fill"></i>Store</a
                >
                <a href="./Sign_In.php"
                    ><i class="bi bi-box-arrow-right"></i>Sign Out</a
                >
            </div>
        </div>

        <div class="main-content">

        </div>

</body>
</html>