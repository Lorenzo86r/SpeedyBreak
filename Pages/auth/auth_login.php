<?php
session_start();
require 'db.php';

if(isset($_POST["username"]) && isset($_POST["password"])){
    $username = $_POST["username"];
    $password = $_POST["password"];

    // 1. Fetch the user info by username
    $sql = "SELECT id, username, password_hash FROM hash_users WHERE username = :username";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login Successful
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["username"] = $user['username'];
            
            // Redirect to management page
            header("Location: ../../index.html");
            exit();
        } else {
            // Invalid credentials
            header("Location: login.php?error=invalid");
            exit();
        }
    } catch (PDOException $e) {
        // classDBBiso error
        header("Location: login.php?error=db");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
