<?php

session_start();

require_once __DIR__ . '/Configure/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = $_POST['email'];
    $password = $_POST['password'];


    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();


    if ($user && password_verify($password, $user['password'])) {


        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];


        header('Location: Store.php');
        exit;

    } else {

        $error = 'Invalid email or password.';
    }
}

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="Sign_In.css" />
        <title>Sign In</title>
    </head>  
    <body>
        <div class="card">
            <div class="auth-wrapper">
                <div class="auth-card">
                    <h1>Sign in</h1>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
 
                    <form method="POST" action="Sign_In.php">
                        <div class="form-group">
                            <label for="email">Sign In with Email</label><br />
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
                        <button type="submit" class="btn-auth">Log In</button>
                    </form>

                    <p class="auth-switch">
                        Don't have an account?
                        <a href="/Sign_Up.php">Sign Up</a>
                    </p>
                </div>
            </div>
        </div>
    </body>

   
</html>
