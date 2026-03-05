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
    <div class="container">
     <header>
            <h1>Login</h1>
     </header>
     <main>
         <form action="auth_login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="femail">Username o Email:</label><br>
                <input type="text" id="femail" name="email" required><br>
            </div>
            <div class="form-group">
                <label for="fpassword">Password:</label><br>
                <input type="password" id="fpassword" name="password" required>
            </div>
            <input type="submit" value="Login" class="btn">
        </form> 
        <?php if(isset($_GET['error'])): ?>
            <p style="color: red; text-align: center;">Email o password non validi.</p>
        <?php endif; ?>
        <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
             <p style="color: green; text-align: center;">Registrazione avvenuta con successo! Ora puoi accedere.</p>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 20px;">
            Non hai un account? <a href="signup.php">Registrati qui</a>
        </p>
     </main>
     <footer>
            <p>&copy; 2026 SpeedyBreak</p>
     </footer>
    </div>
  </body>
</html>
