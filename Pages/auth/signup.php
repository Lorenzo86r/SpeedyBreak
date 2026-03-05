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
    <div class="container">
     <header>
            <h1>Registrazione</h1>
     </header>
     <main>
         <form action="auth_signup.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="fusername">Username:</label><br>
                <input type="text" id="fusername" name="username" required><br>
            </div>
            <div class="form-group">
                <label for="femail">Email (@aldini.istruzioneer.it, @avbo.it, @admin.it o @bar.it):</label><br>
                <input type="email" id="femail" name="email" required pattern=".+@(aldini\.istruzioneer\.it|avbo\.it|admin\.it|bar\.it)$" title="Inserisci un'email valida terminante con @aldini.istruzioneer.it, @avbo.it, @admin.it o @bar.it"><br>
            </div>
            <div class="form-group">
                <label for="fpassword">Password:</label><br>
                <input type="password" id="fpassword" name="password" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" id="ftermini" name="termini" required>
                <label for="ftermini" style="font-size: 0.9em;">Accetto i <a href="#">Termini e Condizioni</a></label>
            </div>
            <input type="submit" value="Registrati" class="btn">
        </form> 
        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'exists'): ?>
                <p style="color: red; text-align: center;">Email già registrata.</p>
            <?php elseif($_GET['error'] == 'duplicate_username'): ?>
                <p style="color: red; text-align: center;">Username già in uso. Scegline un altro.</p>
            <?php elseif($_GET['error'] == 'missing_terms'): ?>
                <p style="color: red; text-align: center;">Devi accettare i Termini e Condizioni per registrarti.</p>
            <?php elseif($_GET['error'] == 'db'): ?>
                 <p style="color: red; text-align: center;">Errore del database.</p>
            <?php elseif($_GET['error'] == 'invalid_email'): ?>
                 <p style="color: red; text-align: center;">Dominio email non valido. Utilizzare @aldini.istruzioneer.it, @avbo.it, @admin.it o @bar.it.</p>
            <?php endif; ?>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 20px;">
            Hai già un account? <a href="login.php">Accedi qui</a>
        </p>
     </main>
     <footer>
            <p>&copy; 2026 SpeedyBreak</p>
     </footer>
    </div>
  </body>
</html>
