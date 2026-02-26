<?php
session_start();
require 'db.php';

if(isset($_POST["username"]) && isset($_POST["password"])){
    $newUsername = $_POST["username"];
    $newPassword = $_POST["password"];

    // Validate email domain
    if (!preg_match('/.+@(aldini\.istruzioneer\.it|avbo\.it)$/', $newUsername)) {
        header("Location: signup.php?error=invalid_email");
        exit();
    }

    // 1. Hash the password
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 2. Prepare the SQL statement
    $sql = "INSERT INTO hash_users (username, password_hash) VALUES (:username, :pword)";
    
    try {
        $stmt = $pdo->prepare($sql);
        // 3. Execute with the data
        $stmt->execute([
            'username' => $newUsername,
            'pword' => $hash
        ]);
        
        // Auto-login after successful registration
        $_SESSION["user_id"] = $pdo->lastInsertId();
        $_SESSION["username"] = $newUsername;
        
        // Redirect to homepage
        header("Location: ../../index.php");
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error code 23000 means 'Duplicate Entry'
            header("Location: signup.php?error=exists");
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
