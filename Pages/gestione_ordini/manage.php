<?php
require 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Ordini - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestione Ordini</h1>
        </header>
        <main>
            <h2>Benvenuto, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Sei autenticato correttamente.</p>
            <br>
            <a href="../auth/logout.php" class="btn">Logout</a>
        </main>
        <footer>
            <p>&copy; 2026 SpeedyBreak</p>
        </footer>
    </div>
</body>
</html>
