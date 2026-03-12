<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Gestione Ordini</title>

    <link rel="stylesheet" href="Assets/Styles/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
    
            <div class="brand">
                <img src="Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </div>
    
            <ul class="nav-links">
                <li><a class="active" href="index.php">Home</a></li>
                <li><a href="Pages/creazione_ordine/index_order.php">Ordina</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a href="./Pages/gestione_ordini/manage.php">Gestione Ordini</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a href="./Pages/amministrazione/admin.php">Admin</a></li>
                <?php endif; ?>
                <li>
                    <a class="login-icon" href="./Pages/auth/profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
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