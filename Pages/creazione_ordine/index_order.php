<?php
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
    </head>
    <body>

    <header>
        <h1>🍔 SpeedyBreak - Ordini Bar</h1>
    </header>

    <div class="container">

        <section class="menu">
            <h2>Menu</h2>

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