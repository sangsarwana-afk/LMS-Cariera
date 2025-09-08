<?php
session_start();
require_once '../config/database.php';
require_once '../src/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}

$page_title = "Login - Sistem Perpustakaan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <h1>Sistem Perpustakaan</h1>
            <h2>Login Petugas</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
