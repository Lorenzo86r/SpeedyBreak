<?php
session_start();
// --- CONFIGURAZIONE DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$db = "my_saqlain";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// --- RECUPERO PRODOTTI DAL DB ---
// Prendiamo solo i prodotti che hanno almeno un pezzo in giacenza
$sql = "SELECT id_prodotto, nome, descrizione, prezzo FROM SB_prodotto WHERE giacenza > 0 ORDER BY nome ASC";
$result = $conn->query($sql);
?>

    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SpeedyBreak - Ordini</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        // passaggio stato login al js
        const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container container">
            <a href="../../index.php" class="brand">
                <img src="../../Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </a>
            <ul class="nav-links">
                <li><a class="nav-item" href="../../index.php">Home</a></li>
                <li><a class="nav-item active" href="../creazione_ordine/index_order.php">Ordina</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item" href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                    <li><a class="nav-item" href="../gestione_ordini/storico_ordini.php">Storico</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                <?php endif; ?>
                <li>
                    <?php if(isset($_SESSION["user_id"])): ?>
                        <a class="nav-icon-btn" href="../auth/profile.php" title="Area Personale">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a class="nav-icon-btn" href="../auth/login.php" title="Login">
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
        <div class="container hero text-center" style="background: transparent; border: none; padding-top: var(--space-4); padding-bottom: var(--space-8);">
            <h1>🍔 Ordina! • SpeedyBreak</h1>
            <p>Seleziona i prodotti che desideri e invia l'ordine al bar.</p>
        </div>

        <div class="container flex gap-6" style="align-items: flex-start; flex-wrap: wrap;">
            
            <section class="menu flex-1" style="min-width: 100%">
                <div class="flex justify-between items-center mb-6">
                   <h2 style="font-size: var(--font-size-2xl);">Menu</h2>
                   <span class="badge badge-warning">Max 30 per prodotto</span>
                </div>
                
                
                
                <div class="menu-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="card product-card animate-fade-in">
                            <h3 style="font-size: var(--font-size-lg);"><?= htmlspecialchars($row['nome']) ?></h3>
                            <p class="desc" style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin-top: var(--space-2);"><?= htmlspecialchars($row['descrizione']) ?></p>
                            <p class="price" style="margin-top: auto; padding-top: var(--space-4);">€<?= number_format($row['prezzo'], 2, ',', '.') ?></p>
                            <div class="actions">
                                <button class="btn btn-primary w-full"
                                        onclick="addToCart('<?= addslashes($row['nome']) ?>', <?= $row['prezzo'] ?>)" <?= !isset($_SESSION['user_id']) ? 'disabled title="Effettua il login per ordinare"' : '' ?>>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: -4px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                   Aggiungi
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--color-text-muted);">Nessun prodotto disponibile al momento.</p>
                <?php endif; ?>
                </div>
            </section>
            
        </div>
    </main>

    <!-- FLOATING CART BUTTON -->
    <button id="floating-cart-btn" class="floating-cart-btn" onclick="toggleCartOverlay()" style="display: none;">
        <div class="cart-icon-wrapper">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <span id="floating-cart-count" class="cart-count-badge">0</span>
        </div>
        <span class="cart-btn-text">Vedi Carrello</span>
        <span id="floating-cart-total" class="cart-btn-total">€0.00</span>
    </button>

    <!-- CART OVERLAY / MODAL -->
    <div id="cart-overlay" class="cart-overlay">
        <div class="cart-overlay-content card">
            <header class="cart-overlay-header">
                <h2>🍔 Il tuo Carrello</h2>
                <button class="close-overlay-btn" onclick="toggleCartOverlay()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </header>
            
            <div class="cart-overlay-body flex gap-6" style="flex-wrap: wrap;">
                
                <!-- Lista articoli -->
                <div style="flex: 2; min-width: 300px;">
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">🛒 Riepilogo Ordine</h3>
                    <ul id="cart-list" style="margin-bottom: var(--space-4); min-height: 200px;"></ul>
                </div>
                
                <!-- Opzioni checkout -->
                <div class="cart-checkout-options card" style="flex: 1; min-width: 300px; background-color: var(--color-surface-hover); border: 1px solid var(--color-border); align-self: flex-start;">
                    <div style="margin-bottom: var(--space-6);">
                        <h3 style="font-size: var(--font-size-md); color: var(--color-text); margin-bottom: var(--space-3); display: flex; align-items: center; gap: 8px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            Metodo di Pagamento
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                            <label class="payment-label">
                                <input type="radio" name="payment-method" value="Contanti" checked
                                       style="margin-right: var(--space-2); accent-color: var(--color-primary);">
                                <span>Contanti</span>
                            </label>
                            <label class="payment-label">
                                <input type="radio" name="payment-method" value="Carta"
                                       style="margin-right: var(--space-2); accent-color: var(--color-primary);">
                                <span>Carta di Credito/Debito</span>
                            </label>
                            <label class="payment-label">
                                <input type="radio" name="payment-method" value="Bancomat"
                                       style="margin-right: var(--space-2); accent-color: var(--color-primary);">
                                <span>Bancomat</span>
                            </label>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: var(--space-6);">
                        <h3 style="font-size: var(--font-size-md); color: var(--color-text); margin-bottom: var(--space-3); display: flex; align-items: center; gap: 8px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Note (opzionale)
                        </h3>
                        <textarea id="order-note" rows="3" placeholder="Aggiungi dettagli, es. 'Senza maionese'"
                                  style="width: 100%; padding: var(--space-3); border: 1px solid var(--color-border); border-radius: var(--radius-md); font-family: inherit; font-size: var(--font-size-sm); resize: vertical; background-color: white;"></textarea>
                    </div>
                
                    <div class="checkout-total-box flex justify-between items-center" style="margin-top: auto; padding-top: var(--space-4); border-top: 2px dashed var(--color-border);">
                        <h3 style="font-size: var(--font-size-lg); color: var(--color-text-muted);">Totale</h3>
                        <div id="total" style="font-size: var(--font-size-3xl); font-weight: 800; color: var(--color-primary);">€0.00</div>
                    </div>
                    
                    <button class="btn btn-primary w-full btn-lg mt-6" onclick="sendOrder()" style="font-size: var(--font-size-lg); padding: var(--space-4);">Invia Ordine</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>

    <script src="script.js"></script>
    <style>
        .payment-label {
            display: flex; 
            align-items: center; 
            padding: var(--space-3); 
            border: 1px solid var(--color-border); 
            border-radius: var(--radius-md); 
            cursor: pointer; 
            transition: all 0.2s;
            background-color: white;
            font-size: var(--font-size-sm);
        }

        .payment-label input[type="radio"]:checked + span {
            font-weight: 600;
            color: var(--color-primary);
        }

        .payment-label:has(input[type="radio"]:checked) {
            border-color: var(--color-primary);
            background-color: rgba(249, 115, 22, 0.05); /* --color-primary but opaque */
            box-shadow: 0 0 0 1px var(--color-primary);
        }

        .payment-label:hover {
            border-color: var(--color-primary);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* OVERLAY E FLOATING BUTTON CSS */
        .cart-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            display: none;
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            padding: var(--space-4);
            animation: fadeIn 0.3s ease-out forwards;
        }
        
        .cart-overlay.active {
            display: flex;
        }
        
        .cart-overlay-content {
            width: 100%;
            max-width: 1000px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Header e totali fissi, scroll in mezzo */
        }
        
        .cart-overlay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-4) var(--space-6);
            border-bottom: 1px solid var(--color-border);
            background-color: white;
        }
        
        .cart-overlay-header h2 {
            margin: 0;
            font-size: var(--font-size-xl);
        }
        
        .close-overlay-btn {
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color var(--transition-fast);
        }
        
        .close-overlay-btn:hover {
            background-color: var(--color-surface-hover);
            color: var(--color-secondary);
        }
        
        .cart-overlay-body {
            padding: var(--space-6);
            overflow-y: auto;
            background-color: var(--color-background);
            flex: 1;
        }

        /* Bottone Fluttuante in basso a destra */
        .floating-cart-btn {
            position: fixed;
            bottom: var(--space-6);
            right: var(--space-6);
            z-index: 900;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 50px; /* pill shape */
            padding: var(--space-3) var(--space-5);
            display: flex;
            align-items: center;
            gap: var(--space-3);
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color var(--transition-fast);
        }
        
        .floating-cart-btn:hover {
            transform: scale(1.05) translateY(-5px);
            background: #ea580c;
        }
        
        .floating-cart-btn:active {
            transform: scale(0.95);
        }

        .cart-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .cart-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: white;
            color: var(--color-primary);
            font-size: 11px;
            font-weight: 800;
            width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .cart-btn-text {
            font-weight: 600;
            font-size: var(--font-size-md);
            border-right: 1px solid rgba(255,255,255,0.3);
            padding-right: var(--space-3);
        }

        .cart-btn-total {
            font-weight: 800;
            font-size: var(--font-size-lg);
        }
        
        /* Miglioramento product card in griglia espansa */
        .product-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>