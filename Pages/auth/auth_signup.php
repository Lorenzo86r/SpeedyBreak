<?php
session_start();
require 'db.php';

if(isset($_POST["username"]) && isset($_POST["email"]) && isset($_POST["password"])){
    if (!isset($_POST["termini"])) {
        header("Location: signup.php?error=missing_terms");
        exit();
    }

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $newPassword = $_POST["password"];

    // Validate email domain
    if (!preg_match('/.+@(aldini\.istruzioneer\.it|avbo\.it|admin\.it)$/', $email)) {
        header("Location: signup.php?error=invalid_email");
        exit();
    }

    // Determine role
    $ruolo = preg_match('/.+@admin\.it$/', $email) ? 'admin' : 'customer';

    // 1. Hash the password
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 2. Prepare the SQL statement
    $sql = "INSERT INTO SB_utente (username, email, password_hash, ruolo) VALUES (:username, :email, :pword, :ruolo)";
    
    try {
        $stmt = $pdo->prepare($sql);
        // 3. Execute with the data
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'pword' => $hash,
            'ruolo' => $ruolo
        ]);
        
        // Auto-login after successful registration
        $_SESSION["user_id"] = $pdo->lastInsertId();
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["ruolo"] = $ruolo;
        
        // Redirect to homepage
        header("Location: ../../index.php");
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error code 23000 means 'Duplicate Entry'
            if (strpos($e->getMessage(), 'username') !== false) {
                header("Location: signup.php?error=duplicate_username");
            } else {
                header("Location: signup.php?error=exists");
            }
            exit();
        } else {
            // Log error in real app
            header("Location: signup.php?error=db");
            exit();
        }
    }
} else {
    header("Location: signup.php");
    exit();
}
?>
