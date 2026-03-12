<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Aggiorna Password - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  </head>
  <body>
    <!-- Nav -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="brand">
                <img src="../../Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </div>
            <ul class="nav-links">
                <li><a href="../../index.php">Home</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                <?php endif; ?>
                <li>
                    <a class="active login-icon" href="profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
     <header>
            <h1>Aggiorna Password</h1>
     </header>
     <main>
         <form action="auth_update_password.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="old_password">Vecchia Password:</label><br>
                <input type="password" id="old_password" name="old_password" required><br>
            </div>
            <div class="form-group">
                <label for="new_password">Nuova Password:</label><br>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Conferma Nuova Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <input type="submit" value="Aggiorna Password" class="btn btn-primary" style="color: #222;">
        </form> 
        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'wrong_old'): ?>
                <p style="color: red; text-align: center; margin-top: 15px;">La vecchia password è errata.</p>
            <?php elseif($_GET['error'] == 'mismatch'): ?>
                <p style="color: red; text-align: center; margin-top: 15px;">Le nuove password non corrispondono.</p>
            <?php else: ?>
                <p style="color: red; text-align: center; margin-top: 15px;">Si è verificato un errore rirprova.</p>
            <?php endif; ?>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 20px;">
            <a href="profile.php">Torna all'Area Personale</a>
        </p>
     </main>
    </div>
  </body>
</html>
