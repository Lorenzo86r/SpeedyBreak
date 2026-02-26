<?php
$password = "StudentPassword123";
echo "<h3>The Password: $password</h3>";

// 1. Create the hash (Unsalted)
// We use the generic 'hash' function.
// 'sha256' is the algorithm.
$unsaltedHash = hash('sha256', $password);

echo "<h3>The Unsafe Way (No Salt)</h3>";
echo "<strong>Run 1 hash:</strong> $unsaltedHash <br>";

// 2. Proof of vulnerability (Determinism)
// If we run it again, we get the EXACT same string.
$unsaltedHash2 = hash('sha256', $password);

echo "<br><strong>Run 2 hash:</strong> $unsaltedHash2 <br>";

// Hash the exact same password two times
$hash1 = password_hash($password, PASSWORD_DEFAULT);
$hash2 = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>The Safe Way (Hash + Salt)</h3>";
echo "<strong>Run 1 hash:</strong><br>";
echo $hash1 . "<br><br>";

echo "<strong>Run 2 hash:</strong><br>";
echo $hash2 . "<br><br>";

if ($hash1 !== $hash2) {
    echo "<h3>Conclusion: The hashes are DIFFERENT!</h3>";
    echo "This proves that PHP added a random <strong>salt</strong> automatically.<br>";
    echo "If there was no salt, these two strings would be identical.";
}
?>