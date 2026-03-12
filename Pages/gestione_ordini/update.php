<?php
session_start();

require_once "gestione-ordine.php";

$db = new Database("localhost", "my_saqlain", "root", "");
$message = "";

/* ID ordine */
$id = intval($_GET["id"]);

/* UPDATE ordine */
if (isset($_POST["update"])) {

    $data = [
        "stato"       => $_POST["stato"],
        "metodo"      => $_POST["metodo"] === "" ? null : $_POST["metodo"],
        "nota"        => $_POST["nota"] === "" ? null : $_POST["nota"],
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
    <style>
        .main-content.container-md {
            max-width: 860px;
            margin: 0 auto;
            padding: 24px 20px 40px;
        }
        .center-container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
    </style>
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
            <li><a class="nav-item" href="../ordini/my_ordini.php">I Miei Ordini</a></li>
            <?php if (isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                <li><a class="nav-item active" href="manage.php">Gestione Ordini</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                <li><a class="nav-item" href="../amministrazione/statistiche.php">Statistiche</a></li>
            <?php endif; ?>
            <li>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <a class="nav-icon-btn" href="../auth/profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                <?php else: ?>
                    <a class="nav-icon-btn" href="../auth/login.php" title="Login">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
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

    <?php if ($message): ?>
        <div class="alert <?= strpos(strtolower($message), 'errore') !== false ? 'alert-error' : 'alert-success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- TUTTO CENTRATO -->
    <div class="center-container" style="max-width: 800px; width: 100%;">
        
        <div class="flex justify-between items-center mb-2" style="width: 100%;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <h2 style="font-size: var(--font-size-2xl); margin: 0;">Ordine #<?= str_pad(htmlspecialchars($ordine["id_ordine"]), 4, '0', STR_PAD_LEFT) ?></h2>
                <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #1d4ed8; font-size: 14px; padding: 6px 14px; border-radius: 99px;">
                    <?= htmlspecialchars($ordine['stato']) ?>
                </span>
            </div>
            <a href="manage.php" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg>
                Torna Indietro
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= strpos(strtolower($message), 'errore') !== false ? 'alert-error' : 'alert-success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            <!-- CLIENTE -->
            <div class="card animate-fade-in" style="margin: 0; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border);">
                <h3 style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px; margin-bottom: 16px; font-size: 18px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-primary);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Dettagli Cliente
                </h3>
                <div style="color: var(--color-text-muted); display:flex; flex-direction:column; gap:10px; font-size: 15px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Username:</span> 
                        <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["username"]) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Email:</span> 
                        <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["email"]) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Ruolo:</span> 
                        <span class="badge" style="background:#f3f4f6; color:#374151; font-weight: 500; font-size: 12px;"><?= htmlspecialchars($ordine["ruolo"]) ?></span>
                    </div>
                </div>
            </div>

            <!-- PRODOTTI -->
            <div class="card animate-fade-in" style="margin: 0; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border);">
                <h3 style="display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--color-border); padding-bottom: 12px; margin-bottom: 16px; font-size: 18px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-primary);"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    Prodotti Ordinati
                </h3>
                <div class="table-container" style="box-shadow: none; border: 1px solid var(--color-border); border-radius: 8px; overflow: hidden; margin-bottom: 0;">
                    <table class="table" style="margin:0; font-size: 14px;">
                        <thead style="background: #f8fafc; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border);">
                            <tr>
                                <th style="padding: 10px 14px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Prodotto</th>
                                <th style="text-align:right; padding: 10px 14px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Qt.</th>
                                <th style="text-align:right; padding: 10px 14px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">Prezzo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totale = 0;
                            foreach ($ordine["prodotti"] as $p): 
                                $sub = $p["prezzo"] * $p["quantita"];
                                $totale += $sub;
                            ?>
                                <tr style="border-bottom: 1px solid var(--color-border);">
                                    <td style="padding: 12px 14px;"><?= htmlspecialchars($p["nome"]) ?></td>
                                    <td style="text-align:right; padding: 12px 14px;"><span style="color: var(--color-primary); font-weight: 700;"><?= htmlspecialchars($p["quantita"]) ?>x</span></td>
                                    <td style="text-align:right; padding: 12px 14px; font-weight: 500;">€<?= number_format($sub, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: rgba(16, 185, 129, 0.05);">
                                <td colspan="2" style="text-align:right; padding: 12px 14px; font-weight: 600; font-size: 16px;">Totale dell'ordine:</td>
                                <td style="text-align:right; padding: 12px 14px; font-weight: 700; color: var(--color-secondary); font-size: 18px;">€<?= number_format($totale, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODIFICA ORDINE -->
        <div class="card animate-fade-in" style="margin: 0; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border); border-top: 4px solid var(--color-secondary);">
            <h3 style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px; font-size: 18px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-secondary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Gestione Operativa
            </h3>
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    
                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block; font-size: 14px;">Stato Ordine</label>
                        <select name="stato" class="form-control" style="background-color: #f8fafc;">
                            <option <?= $ordine["stato"]=="In Attesa"?"selected":"" ?>>In Attesa</option>
                            <option <?= $ordine["stato"]=="In preparazione"?"selected":"" ?>>In preparazione</option>
                            <option <?= $ordine["stato"]=="Pronto"?"selected":"" ?>>Pronto</option>
                            <option <?= $ordine["stato"]=="Completato"?"selected":"" ?>>Completato</option>
                            <option <?= $ordine["stato"]=="Cancellato"?"selected":"" ?>>Cancellato</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block; font-size: 14px;">Ritiro Previsto</label>
                        <input type="datetime-local" name="data_ritiro" class="form-control" style="background-color: #f8fafc;"
                               value="<?= $ordine["data_ritiro"] ? date('Y-m-d\TH:i', strtotime($ordine["data_ritiro"])) : '' ?>">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block; font-size: 14px;">Metodo di Pagamento</label>
                        <select name="metodo" class="form-control" style="background-color: #f8fafc;">
                            <option value="">---</option>
                            <option <?= $ordine["metodo"]=="Contanti"?"selected":"" ?>>Contanti</option>
                            <option <?= $ordine["metodo"]=="Carta di Credito"?"selected":"" ?>>Carta di Credito</option>
                        </select>
                    </div>

                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block; font-size: 14px;">Eventuali Note del Cliente</label>
                    <textarea name="nota" class="form-control" rows="3" placeholder="Nessuna nota fornita dal cliente." style="background:#fefce8; border-color: #fef08a; padding: 12px; resize: vertical;"><?= htmlspecialchars($ordine["nota"]) ?></textarea>
                </div>

                <div class="flex justify-between items-center" style="border-top: 1px solid var(--color-border); padding-top: 20px; flex-wrap: wrap; gap: 16px;">
                    <button type="submit" name="delete" class="btn btn-sm" style="background: transparent; color: var(--color-error); border: 1px solid var(--color-error); padding: 8px 16px;"
                            onclick="return confirm('Sei sicuro di eliminare definitivamente l\'ordine dal DB? L\'azione è irreversibile.')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Elimina Dal Database
                    </button>
                    
                    <button type="submit" name="update" class="btn btn-primary" style="padding: 10px 24px; font-size: 16px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Salva Tutte Modifiche
                    </button>
                </div>
            </form>
        </div>

    </div>
</main>

<footer class="global-footer mt-auto">
    <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
</footer>

</body>
</html>