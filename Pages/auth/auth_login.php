<?php
session_start();
require 'db.php';

if(isset($_POST["email"]) && isset($_POST["password"])){
    $login = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT id_utente, username, email, password_hash, ruolo, nome, cognome FROM SB_utente WHERE email = :email OR username = :username";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $login, 'username' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login Successful
            $_SESSION["user_id"] = $user['id_utente'];
            $_SESSION["username"] = $user['username'];
            $_SESSION["email"] = $user['email'];
            $_SESSION["ruolo"] = $user['ruolo'];
            $_SESSION["nome"] = $user['nome'] ?? '';
            $_SESSION["cognome"] = $user['cognome'] ?? '';
            
            header("Location: ../../index.php");
            exit();
        } else {
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
