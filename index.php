<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Gestione Ordini</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="brand">
            <img src="logo.png" alt="Logo Speedy Break">
            <span>Speedy Break</span>
        </div>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="./Pages/creazione_ordine/ordine.html">Ordina</a></li>
        <li class="nav-item"><a class="nav-link" href="./Pages/gestione_ordini/gestione-ordine.php">Gestione Ordini</a></li>
        <li class="nav-item"><a class="nav-link" href="./Pages/amministrazione/admin.php">Admin</a></li>
        <li class="nav-item">
          <?php if(isset($_SESSION["user_id"])): ?>
            <a class="btn btn-danger ms-2" href="./Pages/auth/logout.php">Logout</a>
          <?php else: ?>
            <a class="btn btn-warning ms-2" href="./Pages/auth/login.php">Login</a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
</nav>

<header>
    <div class="hero">
        <h1>Benvenuto in Speedy Break ☕</h1>
    </div>
</header>

<main>

    <section class="presentazione">
        <h2>Presentazione del Progetto</h2>
        <p>
            Questo sito permette di inserire nuovi ordini, visualizzare quelli esistenti
            e controllare lo stato delle richieste in modo efficiente.
        </p>
    </section>

    <section class="cards">
        <div class="card">
            <h3>Organizzazione</h3>
            <p>Gestione ordinata di tutti gli ordini ricevuti.</p>
        </div>

        <div class="card">
            <h3>Velocità</h3>
            <p>Riduce i tempi di attesa e migliora il servizio.</p>
        </div>

        <div class="card">
            <h3>Precisione</h3>
            <p>Diminuisce gli errori negli ordini.</p>
        </div>
    </section>

</main>

<footer>
    <p>Progetto Speedy Break - 5CIN © 2026</p>
</footer>

</body>
</html>