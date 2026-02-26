<?php
require 'classDBBiso.php';

// Simulate login data
$inputUser = 'Student1';
$inputPass = 'MySuperSecretPassword!'; // Try changing this to fail the login

// 1. Fetch the user info by username
$sql = "SELECT id, username, password_hash FROM hash_users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $inputUser]);
$user = $stmt->fetch();

if ($user) {
    // 2. Verify the password hash
    // $user['password_hash'] is what came from the classDBBiso
    if (password_verify($inputPass, $user['password_hash'])) {
        echo "Login Successful! Welcome, " . htmlspecialchars($user['username']) . ".";
        // Start session here usually: session_start(); $_SESSION['user_id'] = $user['id'];
    } else {
        echo "Invalid password.";
    }
} else {
    echo "User not found.";
}
?>