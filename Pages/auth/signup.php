<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrazione - SpeedyBreak</title>
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
                  <h1>Crea un account</h1>
                  <p>Entra a far parte di SpeedyBreak</p>
           </header>
           <form action="auth_signup.php" method="POST">
              <div class="form-group">
                  <label for="fusername" class="form-label">Username</label>
                  <input type="text" id="fusername" name="username" class="form-control" placeholder="Scegli un username" required>
              </div>
              <div class="form-group">
                  <label for="femail" class="form-label">Email Istituzionale</label>
                  <input type="email" id="femail" name="email" class="form-control" placeholder="es. mario.rossi@aldini.istruzioneer.it" required pattern=".+@(aldini\.istruzioneer\.it|avbo\.it|admin\.it|bar\.it)$" title="Inserisci un'email valida terminante con @aldini.istruzioneer.it, @avbo.it, @admin.it o @bar.it">
                  <p class="form-help">Valida solo per i domini scolastici o di amministrazione.</p>
              </div>
              <div class="form-group">
                  <label for="fpassword" class="form-label">Password</label>
                  <input type="password" id="fpassword" name="password" class="form-control" placeholder="Crea una password sicura" required>
              </div>
              <div class="form-group flex items-center gap-2 mt-4">
                  <input type="checkbox" id="ftermini" name="termini" required style="accent-color: var(--color-primary); width: 16px; height: 16px;">
                  <label for="ftermini" class="form-label" style="margin-bottom: 0;">Accetto i <a href="#">Termini e Condizioni</a></label>
              </div>
              <button type="submit" class="btn btn-primary w-full mt-4">Registrati</button>
          </form> 
          <?php if(isset($_GET['error'])): ?>
              <div class="alert alert-error mt-4">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                  <span>
                  <?php if($_GET['error'] == 'exists'): ?>
                      Email già registrata.
                  <?php elseif($_GET['error'] == 'duplicate_username'): ?>
                      Username già in uso. Scegline un altro.
                  <?php elseif($_GET['error'] == 'missing_terms'): ?>
                      Devi accettare i Termini e Condizioni per registrarti.
                  <?php elseif($_GET['error'] == 'db'): ?>
                      Errore del database.
                  <?php elseif($_GET['error'] == 'invalid_email'): ?>
                      Dominio email non valido. Utilizzare @aldini.istruzioneer.it, @avbo.it, @admin.it o @bar.it.
                  <?php endif; ?>
                  </span>
              </div>
          <?php endif; ?>
          <div class="auth-footer">
              <p>Hai già un account? <a href="login.php">Accedi qui</a></p>
          </div>
        </div>
        <footer class="mt-8 text-center" style="color: var(--color-text-muted); font-size: var(--font-size-sm);">
            <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
        </footer>
      </div>
    </div>
  </body>
</html>
