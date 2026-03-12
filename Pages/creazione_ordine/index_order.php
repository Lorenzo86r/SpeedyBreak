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
            
            <section class="menu flex-1" style="min-width: 60%">
                <div class="flex justify-between items-center mb-6">
                   <h2 style="font-size: var(--font-size-2xl);">Menu</h2>
                   <span class="badge badge-warning">Max 30 per prodotto</span>
                </div>
                
                
                
                <div class="menu-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="card product-card animate-fade-in">
                            <h3 style="font-size: var(--font-size-lg);"><?= htmlspecialchars($row['nome']) ?></h3>
                            <p class="desc" style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin-top: var(--space-2);"><?= htmlspecialchars($row['descrizione']) ?></p>
                            <p class="price">€<?= number_format($row['prezzo'], 2, ',', '.') ?></p>
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
            
            
            <section class="cart cart-sidebar card" style="flex: 0 0 320px;">
                <h2 style="font-size: var(--font-size-xl); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">🛒 Carrello</h2>
                <ul id="cart-list" style="margin-bottom: var(--space-4); min-height: 50px;"></ul>
                <div class="divider"></div>

                <div style="margin-bottom: var(--space-4);">
                    <h3 style="font-size: var(--font-size-lg); color: var(--color-text-muted); margin-bottom: var(--space-3);">
                        💳 Metodo di Pagamento</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                        <label style="display: flex; align-items: center; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="payment-method" value="Contanti" checked
                                   style="margin-right: var(--space-2);">
                            <span>Contanti</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="payment-method" value="Carta"
                                   style="margin-right: var(--space-2);">
                            <span>Carta di Credito/Debito</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="payment-method" value="Bancomat"
                                   style="margin-right: var(--space-2);">
                            <span>Bancomat</span>
                        </label>
                    </div>
                </div>
                <div class="divider"></div>

                <div style="margin-bottom: var(--space-4);">
                    <h3 style="font-size: var(--font-size-lg); color: var(--color-text-muted); margin-bottom: var(--space-3);">
                        📝 Note (opzionale)</h3>
                    <textarea id="order-note" rows="3" placeholder="Aggiungi eventuali note o richieste speciali..."
                              style="width: 100%; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); font-family: inherit; font-size: var(--font-size-sm); resize: vertical;"></textarea>
                </div>
                <div class="divider"></div>

                <div class="flex justify-between items-center mb-4">
                    <h3 style="font-size: var(--font-size-lg); color: var(--color-text-muted);">Totale</h3>
                    <div id="total" style="font-size: var(--font-size-2xl); font-weight: 700; color: var(--color-secondary);">€0.00</div>
                </div>
                <button class="btn btn-primary w-full btn-lg" onclick="sendOrder()">Invia Ordine</button>
            </section>
            

        </div>
    </main>

    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>

    <script src="script.js"></script>
    <style>
        input[type="radio"]:checked + span {
            font-weight: 600;
            color: var(--color-primary);
        }

        label:has(input[type="radio"]:checked) {
            border-color: var(--color-primary);
            background-color: rgba(255, 107, 0, 0.05);
        }

        label:hover {
            border-color: var(--color-primary);
            background-color: rgba(255, 107, 0, 0.02);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>