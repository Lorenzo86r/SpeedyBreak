<?php

require_once "gestione-ordine.php";
$db = new Database("localhost", "my_saqlain", "root", "");
$message = "";

/* Se siamo qui → è stato passato un ID */
$id = intval($_GET["id"]);

/* UPDATE ordine */
if (isset($_POST["update"])) {

    $data = [
        "stato" => $_POST["stato"],
        "metodo" => $_POST["metodo"] === "" ? null : $_POST["metodo"],
        "nota" => $_POST["nota"] === "" ? null : $_POST["nota"],
        "data_ritiro" => $_POST["data_ritiro"] === "" ? null : $_POST["data_ritiro"]
    ];

    if ($db->updateOrdine($id, $data)) {
        $message = "Ordine aggiornato con successo!";
    } else {
        $message = "Errore durante l'aggiornamento.";
    }
}

/* DELETE ordine */
if (isset($_POST["delete"])) {
    if ($db->deleteOrdine($id)) {
        header("Location: manage.php");
        exit;
    } else {
        $message = "Errore durante l'eliminazione.";
    }
}

/* Cambio stato rapido */
if (isset($_POST["change_status"])) {
    if ($db->changeStatus($id, $_POST["new_status"])) {
        $message = "Stato aggiornato!";
    } else {
        $message = "Errore nel cambio stato.";
    }
}

/* Recupero ordine */
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
        .msg { color: green; font-weight: bold; }
    </style>
</head>

<body>

<h2>Gestione Ordine #<?= $ordine["id_ordine"]; ?></h2>

<?php if ($message): ?>
    <p class="msg"><?= $message; ?></p>
<?php endif; ?>

<div class="box">
    <h3>Cliente</h3>
    <p><strong>Username:</strong> <?= $ordine["username"]; ?></p>
    <p><strong>Email:</strong> <?= $ordine["email"]; ?></p>
    <p><strong>Ruolo:</strong> <?= $ordine["ruolo"]; ?></p>
</div>

<div class="box">
    <h3>Prodotti Ordinati</h3>

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
                <td><?= $p["quantita"]; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
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

        <label>Metodo di pagamento:</label><br>
        <select name="metodo">
            <option <?= $ordine["metodo"]=="Contanti"?"selected":""; ?>>Contanti</option>
            <option <?= $ordine["metodo"]=="Carta di Credito"?"selected":""; ?>>Carta di Credito</option>
        </select>
        <br><br>

        <label>Nota:</label><br>
        <textarea name="nota"><?= $ordine["nota"]; ?></textarea>
        <br><br>

        <label>Data ritiro:</label><br>
        <input type="datetime-local" name="data_ritiro"
               value="<?= $ordine["data_ritiro"] ? date('Y-m-d\TH:i', strtotime($ordine["data_ritiro"])) : '' ?>">
        <br><br>

        <button type="submit" name="update">Salva Modifiche</button>
        <button type="submit" name="delete" onclick="return confirm('Sei sicuro di eliminare?')">Elimina Ordine</button>

    </form>
</div>

<p><a href="manage.php">← Torna alla lista ordini</a></p>

</body>
</html>
