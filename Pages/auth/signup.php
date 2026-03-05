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
                <label for="fnome">Nome:</label><br>
                <input type="text" id="fnome" name="nome" required><br>
            </div>
            <div class="form-group">
                <label for="fcognome">Cognome:</label><br>
                <input type="text" id="fcognome" name="cognome" required><br>
            </div>
            <div class="form-group">
                <label for="femail">Email (@aldini.istruzioneer.it, @avbo.it o @admin.it):</label><br>
                <input type="email" id="femail" name="email" required pattern=".+@(aldini\.istruzioneer\.it|avbo\.it|admin\.it)$" title="Inserisci un'email valida terminante con @aldini.istruzioneer.it, @avbo.it o @admin.it"><br>
            </div>
            <div class="form-group">
                <label for="ftelefono">Telefono (Opzionale):</label><br>
                <input type="text" id="ftelefono" name="telefono"><br>
            </div>
            <div class="form-group">
                <label for="fpassword">Password:</label><br>
                <input type="password" id="fpassword" name="password" required>
            </div>
            <input type="submit" value="Registrati" class="btn">
        </form> 
        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error'] == 'exists'): ?>
                <p style="color: red; text-align: center;">Email già registrata.</p>
            <?php elseif($_GET['error'] == 'db'): ?>
                 <p style="color: red; text-align: center;">Errore del database.</p>
            <?php elseif($_GET['error'] == 'invalid_email'): ?>
                 <p style="color: red; text-align: center;">Dominio email non valido. Utilizzare @aldini.istruzioneer.it, @avbo.it o @admin.it.</p>
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
