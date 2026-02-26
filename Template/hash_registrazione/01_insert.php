<?php
require 'classDBBiso.php';

// Simulate form data (In a real form, use $_POST['username'] and $_POST['password'])
$newUsername = 'Student1';
$newPassword = 'MySuperSecretPassword!';

// 1. Hash the password
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// 2. Prepare the SQL statement
// We use :username and :pword as placeholders
$sql = "INSERT INTO hash_users (username, password_hash) VALUES (:username, :pword)";
$stmt = $pdo->prepare($sql);

try {
    // 3. Execute with the data
    $stmt->execute([
        'username' => $newUsername,
        'pword' => $hash
    ]);
    echo "User <strong>$newUsername</strong> registered successfully!";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Error code 23000 means 'Duplicate Entry'
        echo "Error: That username is already taken.";
    } else {
        echo "Database error: " . $e->getMessage();
    }
}
?>