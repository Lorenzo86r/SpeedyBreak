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
    <div class="main-content flex justify-center items-center">
      <div class="auth-container">
        <div class="auth-card animate-fade-in">
           <header class="auth-header">
                  <h1>Aggiorna Password</h1>
                  <p>Modifica la tua password di accesso</p>
           </header>
           <form action="auth_update_password.php" method="POST">
              <div class="form-group">
                  <label for="old_password" class="form-label">Vecchia Password</label>
                  <input type="password" id="old_password" name="old_password" class="form-control" placeholder="Inserisci la vecchia password" required>
              </div>
              <div class="form-group">
                  <label for="new_password" class="form-label">Nuova Password</label>
                  <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Inserisci la nuova password" required>
              </div>
              <div class="form-group">
                  <label for="confirm_password" class="form-label">Conferma Nuova Password</label>
                  <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ripeti la nuova password" required>
              </div>
              <button type="submit" class="btn btn-primary w-full mt-4">Aggiorna Password</button>
          </form> 
          <?php if(isset($_GET['error'])): ?>
              <div class="alert alert-error mt-4">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                  <span>
                  <?php if($_GET['error'] == 'wrong_old'): ?>
                      La vecchia password è errata.
                  <?php elseif($_GET['error'] == 'mismatch'): ?>
                      Le nuove password non corrispondono.
                  <?php else: ?>
                      Si è verificato un errore, riprova.
                  <?php endif; ?>
                  </span>
              </div>
          <?php endif; ?>
          <div class="auth-footer mt-6">
              <a href="profile.php" class="btn btn-secondary w-full">Torna all'Area Personale</a>
          </div>
        </div>
      </div>
    </div>
    
    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>
  </body>
</html>
