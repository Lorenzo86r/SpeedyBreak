<?php
session_start();
require 'db.php';

if(isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["nome"]) && isset($_POST["cognome"])){
    $email = $_POST["email"];
    $newPassword = $_POST["password"];
    $nome = $_POST["nome"];
    $cognome = $_POST["cognome"];
    $telefono = $_POST["telefono"] ?? null;

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
    $sql = "INSERT INTO SB_utente (nome, cognome, email, password_hash, telefono, ruolo) VALUES (:nome, :cognome, :email, :pword, :telefono, :ruolo)";
    
    try {
        $stmt = $pdo->prepare($sql);
        // 3. Execute with the data
        $stmt->execute([
            'nome' => $nome,
            'cognome' => $cognome,
            'email' => $email,
            'pword' => $hash,
            'telefono' => $telefono,
            'ruolo' => $ruolo
        ]);
        
        // Auto-login after successful registration
        $_SESSION["user_id"] = $pdo->lastInsertId();
        $_SESSION["email"] = $email;
        $_SESSION["ruolo"] = $ruolo;
        
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
