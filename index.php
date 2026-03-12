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
    <header class="hero" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%); position: relative; overflow: hidden; border-bottom: 1px solid var(--color-border); padding: 80px 20px;">
        <!-- decorative background elements -->
        <div style="position: absolute; top: -100px; right: -50px; width: 400px; height: 400px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; filter: blur(60px); z-index: 0;"></div>
        <div style="position: absolute; bottom: -100px; left: -50px; width: 300px; height: 300px; background: rgba(16, 185, 129, 0.08); border-radius: 50%; filter: blur(60px); z-index: 0;"></div>
        
        <div style="position: relative; z-index: 1; text-align: center; max-width: 800px; margin: 0 auto;">
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: white; border-radius: 99px; box-shadow: var(--shadow-sm); margin-bottom: 24px; font-weight: 500; color: var(--color-primary); font-size: var(--font-size-sm); border: 1px solid var(--color-border);" class="animate-fade-in">
                <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                Il servizio bar digitale per la tua scuola
            </div>
            <h1 class="animate-fade-in" style="font-size: clamp(2.5rem, 5vw, 4rem); letter-spacing: -0.02em; line-height: 1.1; margin-bottom: 24px; animation-delay: 0.1s; opacity: 0; animation-fill-mode: forwards;">
                La Pausa Perfetta,<br>
                <span style="color: var(--color-primary); background: linear-gradient(90deg, var(--color-primary), #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Senza Attese.</span> ☕
            </h1>
            <p class="animate-fade-in" style="font-size: var(--font-size-xl); margin: 0 auto; color: var(--color-text-muted); line-height: 1.6; animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards;">
                Il modo più veloce ed efficiente per ordinare le tue colazioni e spuntini direttamente al bar della scuola.
            </p>
            
            <div class="animate-fade-in flex justify-center gap-4 mt-8" style="animation-delay: 0.3s; opacity: 0; animation-fill-mode: forwards; flex-wrap: wrap;">
                <a href="Pages/creazione_ordine/index_order.php" class="btn btn-primary" style="padding: 14px 36px; font-size: var(--font-size-lg); border-radius: 99px; box-shadow: var(--shadow-md); transition: transform 0.2s, box-shadow 0.2s;">
                    Ordina Ora 
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 8px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
                <?php if(!isset($_SESSION["user_id"])): ?>
                    <a href="Pages/auth/login.php" class="btn btn-secondary" style="padding: 14px 36px; font-size: var(--font-size-lg); border-radius: 99px; background: white; border: 1px solid var(--color-border); transition: all 0.2s;">Accedi</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container mt-12 mb-12">
        <section class="text-center mb-12">
            <h2 style="font-size: var(--font-size-3xl); margin-bottom: var(--space-4); letter-spacing: -0.01em;">Perché usare SpeedyBreak?</h2>
            <p style="font-size: var(--font-size-lg); color: var(--color-text-muted); max-width: 600px; margin: 0 auto;">
                Semplifichiamo la gestione degli ordini per offrirti un servizio migliore ogni giorno.
            </p>
        </section>

        <section class="flex justify-center gap-6 mt-8" style="flex-wrap: wrap;">
            <div class="card flex flex-col items-center text-center animate-fade-in" style="flex: 1; min-width: 250px; animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards; padding: var(--space-8) var(--space-6); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                <div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2)); color: var(--color-primary); padding: 20px; border-radius: 24px; margin-bottom: 24px;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <h3 style="font-size: var(--font-size-xl); margin-bottom: 12px; font-weight: 600;">Organizzazione</h3>
                <p style="color: var(--color-text-muted); line-height: 1.6;">Gestione ordinata di tutti gli ordini ricevuti, per non perdere mai una richiesta e dare priorità corretta.</p>
            </div>

            <div class="card flex flex-col items-center text-center animate-fade-in" style="flex: 1; min-width: 250px; animation-delay: 0.3s; opacity: 0; animation-fill-mode: forwards; padding: var(--space-8) var(--space-6); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                 <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2)); color: #10b981; padding: 20px; border-radius: 24px; margin-bottom: 24px;">
                     <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                 </div>
                <h3 style="font-size: var(--font-size-xl); margin-bottom: 12px; font-weight: 600;">Velocità</h3>
                <p style="color: var(--color-text-muted); line-height: 1.6;">Salta la fila. Riduci drasticamente i tempi di attesa al banco e ritira il tuo ordine appena pronto.</p>
            </div>

            <div class="card flex flex-col items-center text-center animate-fade-in" style="flex: 1; min-width: 250px; animation-delay: 0.4s; opacity: 0; animation-fill-mode: forwards; padding: var(--space-8) var(--space-6); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                 <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.2)); color: #f59e0b; padding: 20px; border-radius: 24px; margin-bottom: 24px;">
                     <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                 </div>
                <h3 style="font-size: var(--font-size-xl); margin-bottom: 12px; font-weight: 600;">Precisione</h3>
                <p style="color: var(--color-text-muted); line-height: 1.6;">Diminuisce drasticamente tutti gli errori negli ordini o nelle variazioni per un'esperienza perfetta.</p>
            </div>
        </section>
    </div>
</main>

<footer class="global-footer mt-auto">
    <p>Progetto Speedy Break - 5CIN &copy; 2026</p>
</footer>

</body>
</html>