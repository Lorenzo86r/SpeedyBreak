<!DOCTYPE html>
<html lang="it">

    <head>
        <meta charset="UTF-8">
        <title>SpeedyBreak</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <nav class="navbar">
            <div class="nav-container">

                <div class="brand">
                    <img src="../../Assets/Images/logo.png" alt="Logo Speedy Break">
                    <span>Speedy Break</span>
                </div>

                <ul class="nav-links">
                    <li><a class="active" href="../../index.php">Home</a></li>
                    <li><a href="index_order.php">Ordina</a></li>
                    <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                    <li><a href="../amministrazione/admin.php">Admin</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION["user_id"])): ?>
                    <li><a class="login-btn" style="background-color: #dc3545;" href="../auth/logout.php">Logout</a></li>
                    <?php else: ?>
                    <li><a class="login-btn" href="../auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>

            </div>
        </nav>

        <header>
            <h1>🍔 Ordina • SpeedyBreak</h1>
        </header>

        <div class="container">

            <section class="menu">

                <h2>Menu</h2>

                <div class="product">
                    <h3>Panino</h3>
                    <p>€3.00</p>
                    <button onclick="addToCart('Panino',3)">Aggiungi</button>
                </div>

                <div class="product">
                    <h3>Pizza</h3>
                    <p>€5.00</p>
                    <button onclick="addToCart('Pizza',5)">Aggiungi</button>
                </div>

                <div class="product">
                    <h3>Succo</h3>
                    <p>€2.00</p>
                    <button onclick="addToCart('Succo',2)">Aggiungi</button>
                </div>

                <div class="product">
                    <h3>Caffè</h3>
                    <p>€1.20</p>
                    <button onclick="addToCart('Caffè',1.2)">Aggiungi</button>
                </div>

            </section>


            <section class="cart">

                <h2>🛒 Carrello</h2>

                <ul id="cart-list"></ul>

                <h3 id="total">Totale: €0.00</h3>

                <button class="order-btn" onclick="sendOrder()">
                    Invia ordine
                </button>
            </section>

        </div>
        <script src="script.js"></script>

    </body>
</html>