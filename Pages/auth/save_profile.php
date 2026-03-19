<?php
session_start();
require 'db.php';

// Only authenticated users can update their profile
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome']) && isset($_POST['cognome'])) {
    $user_id = intval($_SESSION["user_id"]);
    $email = $_SESSION["email"];
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);

    // Validate non-empty
    if (empty($nome) || empty($cognome)) {
        header("Location: profile.php?error=empty_name");
        exit();
    }

    // Check if the user has an org email — if so, they cannot change nome/cognome
    $org_domains = ['aldini.istruzioneer.it', 'avbo.it'];
    $email_domain = substr(strrchr($email, '@'), 1);

    if (in_array(strtolower($email_domain), $org_domains)) {
        header("Location: profile.php?error=org_readonly");
        exit();
    }

    // Update nome and cognome in the database
    try {
        $stmt = $pdo->prepare("UPDATE SB_utente SET nome = :nome, cognome = :cognome WHERE id_utente = :id");
        $stmt->execute([
            'nome' => $nome,
            'cognome' => $cognome,
            'id' => $user_id
        ]);

        // Update session
        $_SESSION["nome"] = $nome;
        $_SESSION["cognome"] = $cognome;

        header("Location: profile.php?msg=profile_saved");
        exit();

    }
    catch (PDOException $e) {
        header("Location: profile.php?error=db");
        exit();
    }

}
else {
    header("Location: profile.php");
    exit();
}
?>