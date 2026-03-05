<?php
session_start();
require 'db.php';

if(isset($_POST["email"]) && isset($_POST["password"])){
    $email = $_POST["email"];
    $password = $_POST["password"];

    // 1. Fetch the user info by email
    $sql = "SELECT id_utente, email, password_hash, ruolo FROM SB_utente WHERE email = :email";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login Successful
            $_SESSION["user_id"] = $user['id_utente'];
            $_SESSION["email"] = $user['email'];
            $_SESSION["ruolo"] = $user['ruolo'];
            
            // Redirect to management page
            header("Location: ../../index.php");
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
