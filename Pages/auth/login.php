<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  </head>
  <body>
    <div class="main-content flex items-center justify-center">
      <div class="auth-container">
        <div class="auth-card animate-fade-in">
           <header class="auth-header">
                  <h1>Bentornato</h1>
                  <p>Accedi al tuo account SpeedyBreak</p>
           </header>
           <form action="auth_login.php" method="POST">
              <div class="form-group">
                  <label for="femail" class="form-label">Username o Email</label>
                  <input type="text" id="femail" name="email" class="form-control" placeholder="Inserisci username o email" required>
              </div>
              <div class="form-group">
                  <label for="fpassword" class="form-label">Password</label>
                  <input type="password" id="fpassword" name="password" class="form-control" placeholder="Inserisci la password" required>
              </div>
              <button type="submit" class="btn btn-primary w-full mt-4">Login</button>
          </form> 
          <?php if(isset($_GET['error'])): ?>
              <div class="alert alert-error mt-4">
                 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                 Email o password non validi.
              </div>
          <?php endif; ?>
          <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
               <div class="alert alert-success mt-4">
                 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                 Registrazione completata! Ora puoi accedere.
               </div>
          <?php endif; ?>
          <div class="auth-footer">
              <p>Non hai un account? <a href="signup.php">Registrati qui</a></p>
          </div>
        </div>
        <footer class="mt-8 text-center" style="color: var(--color-text-muted); font-size: var(--font-size-sm);">
            <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
        </footer>
      </div>
    </div>
  </body>
</html>
