<?php
require_once __DIR__ . '/Auth/role.php';
http_response_code(403);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied | Giimns</title>
    <link rel="stylesheet" href="No-access.css">
</head>
<body class="center-page">
    <div class="notice">
        <h1>Access denied</h1>
        <p>Your account doesn't have permission to view this page.</p>
        <nav>
            <a class="btn" href="Store.php">Back to the store</a>
            <?php if (isGuest()): ?>
                <a class="btn ghost" href="Sign_In.php">Sign in</a>
            <?php else: ?>
                <a class="btn ghost" href="Sign_Out.php">Sign out</a>
            <?php endif; ?>
        </nav>
    </div>
</body>
</html>
