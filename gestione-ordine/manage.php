
<?php
    require_once "gestione-ordine.php"; // file dove c'è la classe Database

    // 🔹 Connessione DB
    $db = new Database("localhost", "my_vignali", "root", "");

    // 🔹 Recupero ID ordine
    if (!isset($_GET["id"])) {
        die("ID ordine non specificato.");
    }

    $id = intval($_GET["id"]);
    $message = "";

    // ----------------------------
    // GESTIONE AZIONI
    // ----------------------------

    // UPDATE ORDINE
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

    // DELETE ORDINE
    if (isset($_POST["delete"])) {
        if ($db->deleteOrdine($id)) {
            header("Location: lista-ordini.php"); // torna alla lista
            exit;
        } else {
            $message = "Errore durante l'eliminazione.";
        }
    }

    // CAMBIO STATO VELOCE
    if (isset($_POST["change_status"])) {
        if ($db->changeStatus($id, $_POST["new_status"])) {
            $message = "Stato aggiornato!";
        } else {
            $message = "Errore cambio stato.";
        }
    }

    // 🔹 Recupero dati aggiornati
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
            .success { color: green; }
            .error { color: red; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            button { padding: 8px 12px; margin: 5px 0; }
        </style>
    </head>
<body>

    <h2>Gestione Ordine #<?php echo $ordine["id_ordine"]; ?></h2>

    <?php if ($message): ?>
        <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="box">
        <h3>Dati Cliente</h3>
        <p><strong>Nome:</strong> <?php echo $ordine["nome"] . " " . $ordine["cognome"]; ?></p>
        <p><strong>Email:</strong> <?php echo $ordine["email"]; ?></p>
    </div>

    <div class="box">
        <h3>Prodotti Ordinati</h3>
        <table>
            <tr>
                <th>Prodotto</th>
                <th>Prezzo</th>
                <th>Quantità</th>
            </tr>

            <?php foreach ($ordine["prodotti"] as $prodotto): ?>
            <tr>
                <td><?php echo $prodotto["nome"]; ?></td>
                <td>€ <?php echo $prodotto["prezzo"]; ?></td>
                <td><?php echo $prodotto["quantità"]; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="box">
        <h3>Modifica Ordine</h3>

        <form method="POST">

            <label>Stato:</label><br>
            <select name="stato">
                <option <?php if($ordine["stato"]=="In Preparazione") echo "selected"; ?>>In Preparazione</option>
                <option <?php if($ordine["stato"]=="Completato") echo "selected"; ?>>Completato</option>
                <option <?php if($ordine["stato"]=="Annullato") echo "selected"; ?>>Annullato</option>
            </select>
            <br><br>

            <label>Metodo di pagamento:</label><br>
            <input type="text" name="metodo" value="<?php echo $ordine["metodo"]; ?>">
            <br><br>

            <label>Nota:</label><br>
            <textarea name="nota"><?php echo $ordine["nota"]; ?></textarea>
            <br><br>

            <label>Data ritiro:</label><br>
            <input type="datetime-local" name="data_ritiro"
                value="<?php echo date('Y-m-d\TH:i', strtotime($ordine["data_ritiro"])); ?>">
            <br><br>

            <button type="submit" name="update">Salva Modifiche</button>
            <button type="submit" name="delete" onclick="return confirm('Sei sicuro?')">
                Elimina Ordine
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

</body>
</html>