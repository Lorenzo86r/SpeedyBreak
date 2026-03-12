<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        header("Location: update_password.php?error=mismatch");
        exit();
    }

    require_once 'db.php';
    
    try {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['password_hash'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $update_stmt->bindParam(':hash', $new_password_hash);
            $update_stmt->bindParam(':id', $_SESSION['user_id']);
            $update_stmt->execute();

            header("Location: profile.php?msg=pwd_success");
            exit();
        } else {
            header("Location: update_password.php?error=wrong_old");
            exit();
        }
    } catch(PDOException $e) {
        header("Location: update_password.php?error=db");
        exit();
    }
}
?>
