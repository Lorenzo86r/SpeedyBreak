<?php
session_start();

/* ---------------------------------------------------------
   ACCESSO CONSENTITO SOLO AD ADMIN E BARISTA
--------------------------------------------------------- */
if (!isset($_SESSION["ruolo"]) || 
   ($_SESSION["ruolo"] !== 'admin' && $_SESSION["ruolo"] !== 'barista')) 
{
    header("Location: ../../index.php");
    exit();
}

require_once "gestione-ordine.php";

/* ---------------------------------------------------------
   CONNESSIONE AL DATABASE
--------------------------------------------------------- */
$db = new Database("localhost", "my_saqlain", "root", "");

/* Otteniamo tutte le righe degli ordini attivi (una riga per prodotto) */
$righe = $db->getAllOrdiniAttivi();

/* ---------------------------------------------------------
   COSTRUZIONE STRUTTURA GERARCHICA:
   utenti[id_utente] → [
        username, email,
        ordini[id_ordine] → [
            data_ordine, data_ritiro, stato, 
            prodotti[] → [nome, quantita]
        ]
   ]
--------------------------------------------------------- */

$utenti = [];

foreach ($righe as $r) {

    $uid = $r["id_utente"];   // ID utente
    $oid = $r["id_ordine"];   // ID ordine

    /* Se l'utente non è ancora stato inserito, lo creiamo */
    if (!isset($utenti[$uid])) {
        $utenti[$uid] = [
            "username" => $r["username"],
            "email"    => $r["email"],
            "ordini"   => []
        ];
    }

    /* Se l'ordine non è ancora stato inserito per questo utente, lo creiamo */
    if (!isset($utenti[$uid]["ordini"][$oid])) {
        $utenti[$uid]["ordini"][$oid] = [
            "id_ordine"   => $oid,
            "data_ordine" => $r["data_ordine"],
            "data_ritiro" => $r["data_ritiro"],
            "stato"       => $r["stato"],
            "prodotti"    => []
        ];
    }

    /* Aggiungiamo il prodotto all'ordine */
    $utenti[$uid]["ordini"][$oid]["prodotti"][] = [
        "nome"     => $r["nome"],
        "quantita" => $r["quantita"]
    ];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestione Ordini</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">

    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #fafafa;
        }

        /* CARD UTENTE */
        .user-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 35px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .user-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* BLOCCO ORDINE */
        .order-block {
            background: #f7f7f7;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .order-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .order-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        /* PRODOTTI */
        .product-item {
            font-size: 16px;
            margin-left: 15px;
        }

        /* STATO ORDINE */
        .status {
            padding: 6px 10px;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .stato-In\ preparazione { background: #ff9800; }
        .stato-Pronto          { background: #28a745; }
        .stato-In\ attesa      { background: #007bff; }

        /* PULSANTE GESTIONE */
        .button-manage {
            padding: 10px 14px;
            background: #007bff;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
        }

        .button-manage:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">

            <div class="brand">
                <img src="../../Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </div>

            <ul class="nav-links">
                <li><a class="active" href="../../index.php">Home</a></li>
                <li><a href="../creazione_ordine/index_order.php">Ordina</a></li>
                <li><a href="manage.php">Gestione Ordini</a></li>
                <li><a href="storico_ordini.php">Storico</a></li>
                <li><a class="login-btn" style="background-color: #dc3545;" href="../auth/logout.php">Logout</a></li>
            </ul>

        </div>
    </nav>

    <h2 style="margin-bottom:20px;">Ordini Attivi</h2>

    <!-- CARD PER OGNI UTENTE -->
    <?php foreach ($utenti as $utente): ?>

        <div class="user-card">

            <!-- INTESTAZIONE UTENTE -->
            <div class="user-header">
                <?= $utente["username"] ?>
                <span style="font-size:14px; color:#777;">
                    (<?= $utente["email"] ?>)
                </span>
            </div>

            <!-- ORDINI DELL'UTENTE -->
            <?php foreach ($utente["ordini"] as $ordine): ?>

                <div class="order-block">

                    <!-- TITOLO ORDINE -->
                    <div class="order-title">
                        Ordine #<?= $ordine["id_ordine"] ?>
                    </div>

                    <!-- STATO ORDINE -->
                    <div class="status stato-<?= str_replace(' ', '\ ', $ordine["stato"]) ?>">
                        <?= $ordine["stato"] ?>
                    </div>

                    <!-- INFO ORDINE -->
                    <div class="order-info">
                        Ordinato il: <strong><?= $ordine["data_ordine"] ?></strong><br>
                        Ritiro previsto: <strong><?= $ordine["data_ritiro"] ?></strong><br>                       
                    </div>

                    <!-- PRODOTTI -->
                    <div>
                        <strong>Prodotti:</strong><br>

                        <?php foreach ($ordine["prodotti"] as $p): ?>
                            <div class="product-item">
                                • <?= $p["nome"] ?> × <?= $p["quantita"] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <br>

                    <!-- PULSANTE GESTIONE -->
                    <a class="button-manage" href="update.php?id=<?= $ordine["id_ordine"] ?>">
                        Gestisci Ordine
                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endforeach; ?>

</body>
</html>


