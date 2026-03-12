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
        <div class="nav-container container">
    
            <a href="index.php" class="brand">
                <img src="Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </a>
    
            <ul class="nav-links">
                <li><a class="nav-item active" href="index.php">Home</a></li>
                <li><a class="nav-item" href="Pages/creazione_ordine/index_order.php">Ordina</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item" href="Pages/gestione_ordini/manage.php">Gestione Ordini</a></li>
                    <li><a class="nav-item" href="Pages/gestione_ordini/storico_ordini.php">Storico</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="Pages/amministrazione/admin.php">Admin</a></li>
                <?php endif; ?>
                <li>
                    <?php if(isset($_SESSION["user_id"])): ?>
                        <a class="nav-icon-btn" href="Pages/auth/profile.php" title="Area Personale">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a class="nav-icon-btn" href="Pages/auth/login.php" title="Login">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
    
        </div>
    </nav>

<main class="main-content">
    <header class="hero">
        <h1 class="animate-fade-in">Benvenuto in Speedy Break ☕</h1>
        <p class="animate-fade-in" style="animation-delay: 0.1s; opacity: 0; animation-fill-mode: forwards;">Il modo più veloce ed efficiente per ordinare le tue colazioni e spuntini direttamente al bar della scuola.</p>
    </header>

    <div class="container mt-12">
        <section class="text-center mb-12">
            <h2 style="font-size: var(--font-size-3xl); margin-bottom: var(--space-4);">Presentazione del Progetto</h2>
            <p style="font-size: var(--font-size-lg); color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
                Questo sito permette di inserire nuovi ordini, visualizzare quelli esistenti
                e controllare lo stato delle richieste in modo efficiente.
            </p>
        </section>

        <section class="flex justify-center gap-6 mt-8" style="flex-wrap: wrap;">
            <div class="card flex flex-col items-center text-center animate-fade-in" style="flex: 1; min-width: 250px; animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards;">
                <div style="background: var(--color-primary-light); color: var(--color-primary); padding: 16px; border-radius: 50%; margin-bottom: 20px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <h3 style="font-size: var(--font-size-xl); margin-bottom: 12px;">Organizzazione</h3>
                <p style="color: var(--color-text-muted);">Gestione ordinata di tutti gli ordini ricevuti, per non perdere mai una richiesta.</p>
            </div>

            <div class="card flex flex-col items-center text-center animate-fade-in" style="flex: 1; min-width: 250px; animation-delay: 0.3s; opacity: 0; animation-fill-mode: forwards;">
                 <div style="background: var(--color-primary-light); color: var(--color-primary); padding: 16px; border-radius: 50%; margin-bottom: 20px;">
                     <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                 </div>
                <h3 style="font-size: var(--font-size-xl); margin-bottom: 12px;">Velocità</h3>
                <p style="color: var(--color-text-muted);">Riduce i tempi di attesa al banco e migliora il servizio generale.</p>
            </div>

            <div class="card flex flex-col items-center text-center animate-fade-in" style="flex: 1; min-width: 250px; animation-delay: 0.4s; opacity: 0; animation-fill-mode: forwards;">
                 <div style="background: var(--color-primary-light); color: var(--color-primary); padding: 16px; border-radius: 50%; margin-bottom: 20px;">
                     <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                 </div>
                <h3 style="font-size: var(--font-size-xl); margin-bottom: 12px;">Precisione</h3>
                <p style="color: var(--color-text-muted);">Diminuisce gli errori negli ordini per un'esperienza perfetta.</p>
            </div>
        </section>
    </div>
</main>

<footer class="global-footer mt-auto">
    <p>Progetto Speedy Break - 5CIN &copy; 2026</p>
</footer>

</body>
</html>