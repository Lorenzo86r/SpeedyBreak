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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Ordine - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <li><a class="nav-item" href="../creazione_ordine/index_order.php">Ordina</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item active" href="manage.php">Gestione Ordini</a></li>
                    <li><a class="nav-item" href="storico_ordini.php">Storico</a></li>
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

    <main class="main-content container-md">
        <div class="flex justify-between items-center mb-6">
            <h2 style="font-size: var(--font-size-2xl);">Gestione Ordine #<?= htmlspecialchars($ordine["id_ordine"]) ?></h2>
            <a href="manage.php" class="btn btn-secondary">← Torna alla lista ordini</a>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= strpos(strtolower($message), 'errore') !== false ? 'alert-error' : 'alert-success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="flex gap-6" style="flex-wrap: wrap;">
            
            <!-- SINISTRA -->
            <div style="flex: 1; min-width: 300px;" class="flex flex-col gap-6">
                <!-- Box Cliente -->
                <div class="card animate-fade-in" style="animation-duration: 0.3s;">
                    <h3 style="font-size: var(--font-size-xl); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">Cliente</h3>
                    <div style="color: var(--color-text-muted);">
                        <p class="mb-2"><strong style="color: var(--color-text);">Username:</strong> <?= htmlspecialchars($ordine["username"]) ?></p>
                        <p class="mb-2"><strong style="color: var(--color-text);">Email:</strong> <?= htmlspecialchars($ordine["email"]) ?></p>
                        <p><strong style="color: var(--color-text);">Ruolo:</strong> <?= htmlspecialchars($ordine["ruolo"]) ?></p>
                    </div>
                </div>

                <!-- Box Prodotti -->
                <div class="card animate-fade-in" style="animation-duration: 0.4s;">
                    <h3 style="font-size: var(--font-size-xl); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">Prodotti Ordinati</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Prodotto</th>
                                    <th>Prezzo</th>
                                    <th style="text-align: right;">Qt.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ordine["prodotti"] as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p["nome"]) ?></td>
                                        <td>€ <?= htmlspecialchars($p["prezzo"]) ?></td>
                                        <td style="text-align: right;"><?= htmlspecialchars($p["quantita"]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- DESTRA -->
            <div style="flex: 1; min-width: 300px;" class="flex flex-col gap-6">
                <!-- Modifica rapida info/stato -->
                <div class="card animate-fade-in" style="animation-duration: 0.3s;">
                    <h3 style="font-size: var(--font-size-xl); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">Aggiustamento Stato</h3>

                    <form method="POST" class="flex gap-2">
                        <select name="new_status" class="form-control" style="flex: 1;">
                            <option>In Attesa</option>
                            <option>In Preparazione</option>
                            <option>Pronto</option>
                            <option>Completato</option>
                            <option>Annullato</option>
                        </select>
                        <button type="submit" name="change_status" class="btn btn-primary">Applica</button>
                    </form>
                </div>

                <!-- Modifica Ordine -->
                <div class="card animate-fade-in" style="animation-duration: 0.5s;">
                    <h3 style="font-size: var(--font-size-xl); margin-bottom: var(--space-4); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-2);">Modifica Detttagli Ordine</h3>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Stato:</label>
                            <select name="stato" class="form-control">
                                <option <?= $ordine["stato"]=="In Attesa"?"selected":"" ?>>In Attesa</option>
                                <option <?= $ordine["stato"]=="In Preparazione"?"selected":"" ?>>In Preparazione</option>
                                <option <?= $ordine["stato"]=="Pronto"?"selected":"" ?>>Pronto</option>
                                <option <?= $ordine["stato"]=="Completato"?"selected":"" ?>>Completato</option>
                                <option <?= $ordine["stato"]=="Annullato"?"selected":"" ?>>Annullato</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Metodo di pagamento:</label>
                            <select name="metodo" class="form-control">
                                <option value="">---</option>
                                <option <?= $ordine["metodo"]=="Contanti"?"selected":"" ?>>Contanti</option>
                                <option <?= $ordine["metodo"]=="Carta di Credito"?"selected":"" ?>>Carta di Credito</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nota:</label>
                            <textarea name="nota" class="form-control" rows="3"><?= htmlspecialchars($ordine["nota"]) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Data ritiro:</label>
                            <input type="datetime-local" name="data_ritiro" class="form-control"
                                   value="<?= $ordine["data_ritiro"] ? date('Y-m-d\TH:i', strtotime($ordine["data_ritiro"])) : '' ?>">
                        </div>

                        <div class="flex flex-col gap-2 mt-4">
                            <button type="submit" name="update" class="btn btn-primary w-full">Salva Modifiche</button>
                            <button type="submit" name="delete" class="btn btn-danger w-full" onclick="return confirm('Sei sicuro di eliminare definitivamente l\'ordine? L\'azione è irreversibile.')">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Elimina Ordine
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>

</body>
</html>
