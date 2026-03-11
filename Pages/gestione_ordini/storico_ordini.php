<?php
session_start();
require_once "gestione-ordine.php";

/* ---------------------------------------------------------
   CONNESSIONE AL DATABASE
--------------------------------------------------------- */
$db = new Database("localhost", "my_saqlain", "root", "");

/* Otteniamo tutte le righe dello storico (una riga per prodotto) */
$righe = $db->getStoricoOrdini();

/* ---------------------------------------------------------
   STRUTTURA:
   storico[data_ordine] → [
        utenti[id_utente] → [
            username, email,
            ordini[id_ordine] → [
                data_ritiro, metodo, nota,
                prodotti[] → [nome, quantita]
            ]
        ]
   ]
--------------------------------------------------------- */

$storico = [];

foreach ($righe as $r) {

    $data = substr($r["data_ordine"], 0, 10); // yyyy-mm-dd
    $uid  = $r["id_utente"];
    $oid  = $r["id_ordine"];

    /* Se la data non esiste ancora, la creiamo */
    if (!isset($storico[$data])) {
        $storico[$data] = [];
    }

    /* Se l'utente non esiste ancora in quella data, lo creiamo */
    if (!isset($storico[$data][$uid])) {
        $storico[$data][$uid] = [
            "username" => $r["username"],
            "email"    => $r["email"],
            "ordini"   => []
        ];
    }

    /* Se l'ordine non esiste ancora, lo creiamo */
    if (!isset($storico[$data][$uid]["ordini"][$oid])) {
        $storico[$data][$uid]["ordini"][$oid] = [
            "id_ordine"   => $oid,
            "data_ritiro" => $r["data_ritiro"],
            "metodo"      => $r["metodo"] ?? "",
            "nota"        => $r["nota"] ?? "",
            "prodotti"    => []
        ];
    }

    /* Aggiungiamo il prodotto all'ordine */
    $storico[$data][$uid]["ordini"][$oid]["prodotti"][] = [
        "nome"     => $r["nome"],
        "quantita" => $r["quantita"]
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Storico Ordini</title>

    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #fafafa;
        }

        /* CARD PER DATA */
        .date-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 40px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .date-header {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        /* CARD UTENTE */
        .user-block {
            background: #f2f2f2;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .user-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        /* BLOCCO ORDINE */
        .order-block {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #ddd;
        }

        .order-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .order-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
        }

        .product-item {
            font-size: 15px;
            margin-left: 15px;
        }

        .button {
            padding: 8px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<h2>Storico Ordini (Completati)</h2>

<a href="manage.php" class="button">Torna agli ordini attivi</a>
<br><br>

<!-- CARD PER OGNI DATA -->
<?php foreach ($storico as $data => $utenti): ?>

    <div class="date-card">

        <!-- INTESTAZIONE DATA -->
        <div class="date-header">
            📅 <?= $data ?>
        </div>

        <!-- UTENTI DI QUELLA DATA -->
        <?php foreach ($utenti as $utente): ?>

            <div class="user-block">

                <div class="user-title">
                    👤 <?= $utente["username"] ?>
                    <span style="font-size:14px; color:#777;">
                        (<?= $utente["email"] ?>)
                    </span>
                </div>

                <!-- ORDINI DELL'UTENTE -->
                <?php foreach ($utente["ordini"] as $ordine): ?>

                    <div class="order-block">

                        <div class="order-title">
                            Ordine #<?= $ordine["id_ordine"] ?>
                        </div>

                        <div class="order-info">
                            Ritiro: <strong><?= $ordine["data_ritiro"] ?></strong><br>
                            Metodo: <strong><?= $ordine["metodo"] ?></strong><br>
                            Nota: <strong><?= $ordine["nota"] ?></strong>
                        </div>

                        <strong>Prodotti:</strong><br>
                        <?php foreach ($ordine["prodotti"] as $p): ?>
                            <div class="product-item">
                                • <?= $p["nome"] ?> × <?= $p["quantita"] ?>
                            </div>
                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

    </div>

<?php endforeach; ?>

</body>
</html>
