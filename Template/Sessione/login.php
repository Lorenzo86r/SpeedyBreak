<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Sessioni</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
     <form action="crea-sessione.php" method="POST">
      	<label for="femail">Email:</label><br>
      	<input type="email" id="femail" name="email" required><br>
      	<label for="fpassword">Password:</label><br>
		<input type="password" id="fpassword" name="password" required>
       	<input type="submit" value="Login">
    </form> 
  </body>
</html>
