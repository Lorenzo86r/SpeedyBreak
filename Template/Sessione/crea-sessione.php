<?php

// Controllo se i dati sono arrivati con una POST e se nella POST sono presenti "email" e "password"
if(isset($_POST["email"]) && isset($_POST["password"])){
	
    // Nella realtà controllo le informazioni del database
    // ma in questo caso confronto le informazioni inviate da utente con dati statici.
    
    $email = $_POST["email"];
    $password = $_POST["password"];  
    if($email == "mario.rossi@avbo.it" && $password == "password"){
    	// Creo la sessione
    	session_start();
        $_SESSION["email"] = $email;
        $_SESSION["role"] = "admin"; // or "user"
        
        // Porto l'utente alla homepage
        header("Location: homepage.php");
        die();
    }else{
    	header("Location: login.php?e=invalid-login");
        die();
    }
}else{
	header("Location: login.php");
	die();
}