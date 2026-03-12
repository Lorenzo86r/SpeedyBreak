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
    
    <div class="main-content flex justify-center items-center">
      <div class="auth-container">
        <div class="auth-card animate-fade-in">
          <?php if(isset($_SESSION["user_id"])): ?>
              <header class="auth-header" style="background: linear-gradient(135deg, var(--color-primary), #2563eb); border-radius: var(--radius-lg); padding: var(--space-8) var(--space-6); text-align: center; color: white; margin-bottom: var(--space-6); position: relative; overflow: hidden; box-shadow: var(--shadow-md);">
                  <!-- Decorative circle for pattern -->
                  <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                  <div style="position: absolute; bottom: -50px; left: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                  
                  <?php 
                     $username = $_SESSION['username'] ?? 'Utente';
                     $initial = strtoupper(substr($username, 0, 1)); 
                  ?>
                  
                  <div style="position: relative; z-index: 1;">
                      <div style="width: 80px; height: 80px; background: white; color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; margin: 0 auto var(--space-4) auto; box-shadow: 0 4px 14px rgba(0,0,0,0.15);">
                          <?php echo $initial; ?>
                      </div>
                      <h2 style="color: white; font-size: var(--font-size-2xl); margin-bottom: var(--space-1); letter-spacing: -0.01em;">Bentornato, <?php echo htmlspecialchars($username); ?>!</h2>
                      <p style="opacity: 0.85; margin: 0; font-size: var(--font-size-sm);">Gestisci il tuo profilo e le impostazioni.</p>
                  </div>
              </header>
              
              <?php if(isset($_GET['msg']) && $_GET['msg'] == 'pwd_success'): ?>
                  <div class="alert alert-success mb-6 justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Password aggiornata con successo!
                  </div>
              <?php endif; ?>
              
              <div class="flex flex-col gap-4">
                  <a href="update_password.php" class="btn btn-primary w-full p-4 justify-start">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                      </svg>
                      <span>Aggiorna Password</span>
                  </a>
                  <a href="logout.php" class="btn btn-danger w-full p-4 justify-start" style="border: 1px solid #fecaca;">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                          <polyline points="16 17 21 12 16 7"></polyline>
                          <line x1="21" y1="12" x2="9" y2="12"></line>
                      </svg>
                      <span>Logout</span>
                  </a>
              </div>
          <?php else: ?>
              <header class="auth-header" style="text-align: center; margin-bottom: var(--space-6);">
                  <div style="width: 64px; height: 64px; background: var(--color-primary-light); color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4) auto;">
                     <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                  </div>
                  <h2 style="font-size: var(--font-size-2xl); color: var(--color-secondary); margin-bottom: var(--space-2); letter-spacing: -0.01em;">Area Personale</h2>
                  <p style="color: var(--color-text-muted);">Accedi o registrati per gestire i tuoi ordini.</p>
              </header>

              <div class="flex flex-col gap-4 mt-6">
                  <a href="login.php" class="btn btn-primary w-full p-4 justify-start">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                          <polyline points="10 17 15 12 10 7"></polyline>
                          <line x1="15" y1="12" x2="3" y2="12"></line>
                       </svg>
                      <span>Login</span>
                  </a>
                  <a href="signup.php" class="btn btn-secondary w-full p-4 justify-start">
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
        </div>
      </div>
    </div>
    
    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>
  </body>
</html>
