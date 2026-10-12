<?php

session_start();
require_once __DIR__ . '/Auth/role.php';
require_once __DIR__ . '/Configure/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $error = 'That email is already registered.';
    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email,:password)');
        $stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashedPassword]);


        $_SESSION['user'] = [
            'id'    => $pdo->lastInsertId(),
            'name'  => $name,
            'email' => $email,
            'role'  => 'user',
        ];


        header('Location: Store.php');
        exit;
    }

}

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="Sign_Up.css" />
        <title>Sign Up</title>
    </head>
    <body>
        <div class="card">
            <div class="auth-wrapper">
                <div class="auth-card">
                    <h1>Create Your Account</h1>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

                    <form method="POST" action="Sign_Up.php">

                <div class="form-group">
                    <label for="name">Username</label>
                    <input type="text" id="name" name="name"  required>
                </div>

                        <div class="form-group">
                            <label for="email">Email Address</label><br />
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="@example.com"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label><br />
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required
                            />
                        </div>
                        <button type="submit" class="btn-auth">Sign Up</button>
                    </form>

                    <p class="auth-switch">
                        Already have an account?
                        <a href="/Sign_In.php">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </body>

   
</html>
