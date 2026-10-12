<?php
session_start();
require_once __DIR__ . '/Auth/role.php';
requireRole(['user', 'editor', 'admin']);

if (isGuest()){
    header ('location:/Sign_In.php');
    exit;
}

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
                <a href="./Profile.php"><i class="bi bi-person-fill"></i></a>
            </div>
                
                <div class="signout">
                    <a href="Sign_Out.php"
                        ><i class="bi bi-box-arrow-right"></i
                    ></a>
                </div>
            </div>
        </div>

    <div class="banner">
    <img src="Assets/Images/banner_long.png">
    </div>
    
        <script src="Store.js"></script>
    </body>
</html>
