<?php
session_start();
require 'db.php';

if (isset($_POST["username"]) && isset($_POST["email"]) && isset($_POST["password"])) {
    if (!isset($_POST["termini"])) {
        header("Location: signup.php?error=missing_terms");
        exit();
    }

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $newPassword = $_POST["password"];

    //piccola policy di password debole o corta
    if (strlen($newPassword) < 8) {
        header("Location: signup.php?error=weak_password");
        exit();
    }
    if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        header("Location: signup.php?error=weak_password");
        exit();
    }

    // Validate email domain — only organizational emails allowed
    $allowed_domains = ['aldini.istruzioneer.it', 'avbo.it'];
    $email_domain = substr(strrchr($email, '@'), 1);

    if (!in_array(strtolower($email_domain), $allowed_domains)) {
        header("Location: signup.php?error=not_org");
        exit();
    }

    // All valid organizational emails get the 'customer' role
    $ruolo = 'customer';

    // Extract nome and cognome from email (name.surname@domain)
    $local_part = substr($email, 0, strpos($email, '@'));
    $parts = explode('.', $local_part);
    $nome = ucfirst(strtolower($parts[0] ?? ''));
    $cognome = ucfirst(strtolower($parts[1] ?? ''));

    // 1. Hash the password
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 2. Prepare the SQL statement
    $sql = "INSERT INTO SB_utente (username, email, password_hash, ruolo, nome, cognome) VALUES (:username, :email, :pword, :ruolo, :nome, :cognome)";

    try {
        $stmt = $pdo->prepare($sql);
        // 3. Execute with the data
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'pword' => $hash,
            'ruolo' => $ruolo,
            'nome' => $nome,
            'cognome' => $cognome
        ]);

        // Auto-login after successful registration
        $_SESSION["user_id"] = $pdo->lastInsertId();
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["ruolo"] = $ruolo;
        $_SESSION["nome"] = $nome;
        $_SESSION["cognome"] = $cognome;

        // Redirect to homepage
        header("Location: ../../index.php");
        exit();

    }
    catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            if (strpos($e->getMessage(), 'username') !== false) {
                header("Location: signup.php?error=duplicate_username");
            }
            else {
                header("Location: signup.php?error=exists");
            }
            exit();
        }
        else {
            header("Location: signup.php?error=db");
            exit();
        }
    }
}
else {
    header("Location: signup.php");
    exit();
}
?>