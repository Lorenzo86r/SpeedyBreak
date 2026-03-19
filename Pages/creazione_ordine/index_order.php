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

// --- RECUPERO CATEGORIE ---
$cat_sql = "SELECT id_categoria, descrizione FROM SB_categoria ORDER BY descrizione ASC";
$cat_result = $conn->query($cat_sql);
$categorie = [];
while ($c = $cat_result->fetch_assoc()) {
    $categorie[] = $c;
}

// --- RECUPERO PRODOTTI DAL DB ---
$sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, c.descrizione AS categoria
        FROM SB_prodotto p
        JOIN SB_categoria c ON p.id_categoria = c.id_categoria
        WHERE p.giacenza > 0
        ORDER BY p.nome ASC";
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
            const userSaldo = <?= isset($user_saldo) ? $user_saldo : 0 ?>;
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
                    <li><a class="nav-item" href="../ordini/my_ordini.php">I Miei Ordini</a></li>
                    <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                        <li><a class="nav-item" href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                        <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a class="nav-item" href="../amministrazione/statistiche.php">Statistiche</a></li>
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
                    <div class="flex justify-between items-center mb-4">
                       <h2 style="font-size: var(--font-size-2xl);">Menu</h2>
                       <span class="badge badge-warning">Max 30 per prodotto</span>
                    </div>

                    <!-- SEARCH BAR -->
                    <div style="position: relative; margin-bottom: 16px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); pointer-events: none;">
                            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="search-input" placeholder="Cerca prodotto..." class="form-control"
                               style="padding-left: 42px; border-radius: 99px; background: var(--color-surface); border: 1px solid var(--color-border); font-size: 15px; height: 44px;">
                    </div>

                    <!-- CATEGORY FILTERS -->
                    <div id="category-filters" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
                        <button class="filter-pill active" data-category="all" onclick="filterCategory('all', this)">Tutti</button>
                        <?php foreach ($categorie as $cat): ?>
                            <button class="filter-pill" data-category="<?= htmlspecialchars($cat['descrizione']) ?>" onclick="filterCategory('<?= htmlspecialchars($cat['descrizione']) ?>', this)">
                                <?= htmlspecialchars($cat['descrizione']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <p id="no-results" style="display: none; text-align: center; color: var(--color-text-muted); padding: 40px 0; font-size: 15px;">Nessun prodotto trovato per la tua ricerca.</p>

                    <div class="menu-grid">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="card product-card animate-fade-in" data-name="<?= strtolower(htmlspecialchars($row['nome'])) ?>" data-category="<?= htmlspecialchars($row['categoria']) ?>">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <h3 style="font-size: var(--font-size-lg);"><?= htmlspecialchars($row['nome']) ?></h3>
                                    <span class="badge" style="font-size: 11px; background: rgba(59,130,246,0.1); color: #1d4ed8; white-space: nowrap;"><?= htmlspecialchars($row['categoria']) ?></span>
                                </div>
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

                    <?php
                        // Fetch user saldo for payment option
                        $user_saldo = 0;
                        if (isset($_SESSION['user_id'])) {
                            $uid = intval($_SESSION['user_id']);
                            $res_saldo = $conn->query("SELECT saldo FROM SB_utente WHERE id_utente = $uid");
                            if ($res_saldo && $row_s = $res_saldo->fetch_assoc()) {
                                $user_saldo = (float)$row_s['saldo'];
                            }
                        }
                    ?>
                    <div style="margin-bottom: var(--space-4);">
                        <h3 style="font-size: var(--font-size-lg); color: var(--color-text-muted); margin-bottom: var(--space-3);">
                            💳 Metodo di Pagamento</h3>
                        <div style="display: flex; flex-direction: column; gap: var(--space-2);">
                            <label style="display: flex; align-items: center; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="payment-method" value="Contanti" checked
                                       style="margin-right: var(--space-2);">
                                <span>💵 Contanti</span>
                            </label>
                            <label id="saldo-label" style="display: flex; align-items: center; justify-content: space-between; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); transition: all 0.2s; <?= $user_saldo <= 0 ? 'opacity: 0.45; cursor: not-allowed; background: #f9fafb;' : 'cursor: pointer;' ?>">
                                <div style="display: flex; align-items: center;">
                                    <input type="radio" name="payment-method" value="Saldo"
                                           style="margin-right: var(--space-2);" <?= $user_saldo <= 0 ? 'disabled' : '' ?>>
                                    <span>💰 Saldo</span>
                                </div>
                                <span style="font-size: 13px; font-weight: 600; color: <?= $user_saldo > 0 ? '#16a34a' : '#9ca3af' ?>;">€<?= number_format($user_saldo, 2, ',', '.') ?></span>
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
                    <button class="btn btn-primary w-full btn-lg" onclick="openConfirmModal()">Procedi all'Ordine</button>
                </section>


        </main>

        <!-- Modal Conferma Ordine -->
        <div id="confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div class="card animate-fade-in" style="width: 90%; max-width: 500px; padding: var(--space-6); position: relative; max-height: 90vh; overflow-y: auto;">
                <button onclick="closeConfirmModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; cursor: pointer; color: var(--color-text-muted);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>

                <h2 style="font-size: var(--font-size-2xl); margin-bottom: var(--space-4); color: var(--color-secondary); display: flex; align-items: center; gap: 8px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Conferma il tuo Ordine
                </h2>

                <p style="color: var(--color-text-muted); margin-bottom: var(--space-4);">Controlla i dettagli prima di inviare l'ordine in produzione.</p>

                <div style="background: var(--color-background); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-4); border: 1px solid var(--color-border);">
                    <h3 style="font-size: var(--font-size-sm); text-transform: uppercase; color: var(--color-text-muted); letter-spacing: 0.05em; margin-bottom: var(--space-2);">Riepilogo</h3>
                    <ul id="modal-cart-list" style="list-style: none; padding: 0; margin: 0; font-size: var(--font-size-sm);">
                        <!-- Populated by JS -->
                    </ul>
                    <div class="divider" style="margin: var(--space-3) 0;"></div>
                    <div class="flex justify-between items-center font-bold">
                        <span>Totale:</span>
                        <span id="modal-total" style="color: var(--color-primary); font-size: var(--font-size-lg);">€0.00</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3); margin-bottom: var(--space-6); font-size: var(--font-size-sm);">
                    <div style="background: var(--color-background); padding: var(--space-3); border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                        <div style="color: var(--color-text-muted); margin-bottom: 2px;">Metodo</div>
                        <div id="modal-method" style="font-weight: 600;">-</div>
                    </div>
                    <div style="background: var(--color-background); padding: var(--space-3); border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                        <div style="color: var(--color-text-muted); margin-bottom: 2px;">Note</div>
                        <div id="modal-note" style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button class="btn btn-secondary flex-1" onclick="closeConfirmModal()">Annulla</button>
                    <button class="btn btn-primary flex-1" id="confirm-submit-btn" onclick="submitConfirmedOrder()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: -4px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        Conferma
                    </button>
                </div>
            </div>
        </div>

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
            /* Filter Pills */
            .filter-pill {
                padding: 6px 16px;
                border-radius: 99px;
                border: 1px solid var(--color-border);
                background: var(--color-surface);
                color: var(--color-text-muted);
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            }
            .filter-pill:hover {
                border-color: var(--color-primary);
                color: var(--color-primary);
            }
            .filter-pill.active {
                background: var(--color-primary);
                color: white;
                border-color: var(--color-primary);
            }
        </style>

        <script>
            let activeCategory = 'all';

            function filterCategory(cat, btn) {
                activeCategory = cat;
                document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                applyFilters();
            }

            document.getElementById('search-input').addEventListener('input', applyFilters);

            function applyFilters() {
                const query = document.getElementById('search-input').value.toLowerCase().trim();
                const cards = document.querySelectorAll('.product-card');
                let visible = 0;

                cards.forEach(card => {
                    const name = card.dataset.name;
                    const category = card.dataset.category;
                    const matchSearch = !query || name.includes(query);
                    const matchCategory = activeCategory === 'all' || category === activeCategory;

                    if (matchSearch && matchCategory) {
                        card.style.display = '';
                        visible++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
            }
        </script>
    </body>
</html>
<?php $conn->close(); ?>