<?php
require_once "gestione-ordine.php";

$db = new Database("localhost", "", "root", "");
$message = "";


if (!isset($_GET["id"])) {

    // Recupero tutti gli ordini
    $ordini = $db->getAllOrdini();

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Lista Ordini</title>
        <style>
            body { font-family: Arial; margin: 40px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background-color: #f2f2f2; }
            a.button {
                padding: 6px 10px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>

    <h2>Lista Ordini</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Stato</th>
            <th>Metodo</th>
            <th>Azione</th>
        </tr>

        <?php foreach ($ordini as $ordine): ?>
            <tr>
                <td><?= $ordine["id_ordine"] ?></td>
                <td><?= $ordine["data_ordine"] ?></td>
                <td><?= $ordine["stato"] ?></td>
                <td><?= $ordine["metodo"] ?></td>
                <td>
                    <a class="button" href="manage.php?id=<?= $ordine["id_ordine"] ?>">
                        Gestisci
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    </body>
    </html>
    <?php
    exit;
}



$id = intval($_GET["id"]);

// UPDATE
if (isset($_POST["update"])) {

    $data = [
        "stato" => $_POST["stato"],
        "metodo" => $_POST["metodo"],
        "nota" => $_POST["nota"],
        "data_ritiro" => $_POST["data_ritiro"]
    ];

    if ($db->updateOrdine($id, $data)) {
        $message = "Ordine aggiornato con successo!";
    } else {
        $message = "Errore durante l'aggiornamento.";
    }
}

// DELETE
if (isset($_POST["delete"])) {
    if ($db->deleteOrdine($id)) {
        header("Location: manage.php");
        exit;
    } else {
        $message = "Errore durante l'eliminazione.";
    }
}

// CAMBIO STATO
if (isset($_POST["change_status"])) {
    if ($db->changeStatus($id, $_POST["new_status"])) {
        $message = "Stato aggiornato!";
    } else {
        $message = "Errore cambio stato.";
    }
}

$ordine = $db->getOrdineById($id);

if (!$ordine) {
    die("Ordine non trovato.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestione Ordine</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .box { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        button { padding: 8px 12px; margin: 5px 0; }
        .msg { color: green; }
    </style>
</head>
<body>

<h2>Gestione Ordine #<?= $ordine["id_ordine"]; ?></h2>

<?php if ($message): ?>
    <p class="msg"><?= $message; ?></p>
<?php endif; ?>

<div class="box">
    <h3>Cliente</h3>
    <p><strong>Nome:</strong> <?= $ordine["nome"] . " " . $ordine["cognome"]; ?></p>
    <p><strong>Email:</strong> <?= $ordine["email"]; ?></p>
</div>

<div class="box">
    <h3>Prodotti</h3>
    <table>
        <tr>
            <th>Prodotto</th>
            <th>Prezzo</th>
            <th>Quantità</th>
        </tr>
        <?php foreach ($ordine["prodotti"] as $p): ?>
        <tr>
            <td><?= $p["nome"]; ?></td>
            <td>€ <?= $p["prezzo"]; ?></td>
            <td><?= $p["quantità"]; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h3>Modifica Ordine</h3>

    <form method="POST">
        <label>Stato:</label><br>
        <select name="stato">
            <option <?= $ordine["stato"]=="In Preparazione"?"selected":""; ?>>In Preparazione</option>
            <option <?= $ordine["stato"]=="Completato"?"selected":""; ?>>Completato</option>
            <option <?= $ordine["stato"]=="Annullato"?"selected":""; ?>>Annullato</option>
        </select>
        <br><br>

        <label>Metodo:</label><br>
        <input type="text" name="metodo" value="<?= $ordine["metodo"]; ?>">
        <br><br>

        <label>Nota:</label><br>
        <textarea name="nota"><?= $ordine["nota"]; ?></textarea>
        <br><br>

        <label>Data ritiro:</label><br>
        <input type="datetime-local" name="data_ritiro"
            value="<?= date('Y-m-d\TH:i', strtotime($ordine["data_ritiro"])); ?>">
        <br><br>

        <button type="submit" name="update">Salva</button>
        <button type="submit" name="delete" onclick="return confirm('Sei sicuro?')">
            Elimina
        </button>
    </form>
</div>

<div class="box">
    <h3>Cambio Stato Rapido</h3>

    <form method="POST">
        <select name="new_status">
            <option>In Preparazione</option>
            <option>Completato</option>
            <option>Annullato</option>
        </select>

        <button type="submit" name="change_status">Aggiorna Stato</button>
    </form>
</div>

<p><a href="manage.php">← Torna alla lista</a></p>

</body>
</html>