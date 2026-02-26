<?php
// 1. Get the password from the user input (e.g., $_POST['password'])
$userInputPassword = "MySecretPassword123"; 

// 2. Hash the password
// PASSWORD_DEFAULT tells PHP to use the current strongest algorithm (currently Bcrypt).
$hashedPassword = password_hash($userInputPassword, PASSWORD_DEFAULT);

// 3. Store $hashedPassword in your database
// (Pseudo-code for database insertion)
// $sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
// $stmt->execute([$username, $hashedPassword]);

echo "<h3>Registration Successful</h3>";
echo "<strong>Original:</strong> " . $userInputPassword . "<br>";
echo "<strong>Stored in classDBBiso:</strong> " . $hashedPassword;
?>