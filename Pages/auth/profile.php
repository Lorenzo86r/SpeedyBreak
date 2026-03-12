<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Area Personale - SpeedyBreak</title>
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
    
    <div class="container user-dashboard">
      <header>
          <h1>Area Personale</h1>
      </header>
      <main>
          <?php if(isset($_SESSION["user_id"])): ?>
              <div class="welcome-box">
                  <h2>Bentornato, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utente'); ?>!</h2>
                  <p>Gestisci il tuo profilo e l'accesso al tuo account.</p>
              </div>
              
              <?php if(isset($_GET['msg']) && $_GET['msg'] == 'pwd_success'): ?>
                  <p style="color: green; text-align: center; margin-bottom: 20px; font-weight: bold;">Password aggiornata con successo!</p>
              <?php endif; ?>
              
              <div class="dashboard-actions">
                  <a href="update_password.php" class="dashboard-btn btn-primary">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                      </svg>
                      <span>Aggiorna Password</span>
                  </a>
                  <a href="logout.php" class="dashboard-btn btn-danger">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                          <polyline points="16 17 21 12 16 7"></polyline>
                          <line x1="21" y1="12" x2="9" y2="12"></line>
                      </svg>
                      <span>Logout</span>
                  </a>
              </div>
          <?php else: ?>
              <div class="welcome-box">
                  <h2>Benvenuto in SpeedyBreak</h2>
                  <p>Accedi o registrati per gestire i tuoi ordini e il tuo account.</p>
              </div>
              <div class="dashboard-actions">
                  <a href="login.php" class="dashboard-btn btn-primary">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                          <polyline points="10 17 15 12 10 7"></polyline>
                          <line x1="15" y1="12" x2="3" y2="12"></line>
                      </svg>
                      <span>Login</span>
                  </a>
                  <a href="signup.php" class="dashboard-btn btn-secondary">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                          <circle cx="8.5" cy="7" r="4"></circle>
                          <line x1="20" y1="8" x2="20" y2="14"></line>
                          <line x1="23" y1="11" x2="17" y2="11"></line>
                      </svg>
                      <span>Registrati</span>
                  </a>
              </div>
          <?php endif; ?>
      </main>
      <footer>
          <p>&copy; 2026 SpeedyBreak</p>
      </footer>
    </div>
  </body>
</html>
