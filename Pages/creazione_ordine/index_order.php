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
        <link rel="stylesheet" href="style.css">
        <style>
            /* Un piccolo tocco di stile extra per la descrizione */
            .product p.desc {
                font-size: 0.9em;
                color: #666;
                font-style: italic;
            }
            .price {
                font-weight: bold;
                color: #2c3e50;
            }
        </style>

        <script>
            // passaggio stato login al js
            const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
        </script>
    </head>
    <body>
    <ul class="nav-links">
        <li><a class="active" href="../../index.php">Home</a></li>
        <li><a href="../creazione_ordine/index_order.php">Ordina</a></li>
        <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
            <li><a href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
        <?php endif; ?>
        <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
            <li><a href="../amministrazione/admin.php">Admin</a></li>
        <?php endif; ?>
        <li>
            <a class="login-icon" href="../auth/profile.php" title="Area Personale">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>
        </li>
    </ul>

    <header>
        <h1>🍔 Ordina! • SpeedyBreak</h1>
    </header>

    <div class="container">

        <section class="menu">
            <h2>Menu</h2>
            <h3>Max 5 elementi per prodotto</h3><br>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product">
                        <h3><?= htmlspecialchars($row['nome']) ?></h3>
                        <p class="desc"><?= htmlspecialchars($row['descrizione']) ?></p>
                        <p class="price">€<?= number_format($row['prezzo'], 2, ',', '.') ?></p>
                        <button onclick="addToCart('<?= addslashes($row['nome']) ?>', <?= $row['prezzo'] ?>)">
                            Aggiungi
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nessun prodotto disponibile al momento.</p>
            <?php endif; ?>

        </section>

        <section class="cart">
            <h2>🛒 Carrello</h2>
            <ul id="cart-list"></ul>
            <hr>
            <h3 id="total">Totale: €0.00</h3>
            <button class="order-btn" onclick="sendOrder()">Invia Ordine</button>
        </section>

    </div>

    <script src="script.js"></script>
    </body>
    </html>
<?php $conn->close(); ?>