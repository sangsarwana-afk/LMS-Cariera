<?php
require_once '../config/database.php';

function login($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM staff WHERE username = ? AND password = ?");
    $stmt->execute([$username, md5($password)]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['name'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>
