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
</head>

<body>

    <!-- NAVBAR -->
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

    <main class="main-content container">
        <div class="flex justify-between items-center mb-6">
            <h2 style="font-size: var(--font-size-3xl);">Ordini Attivi</h2>
        </div>

        <!-- CARD PER OGNI UTENTE -->
        <?php foreach ($utenti as $utente): ?>
            <div class="card mb-6 animate-fade-in">
                <!-- INTESTAZIONE UTENTE -->
                <div class="mb-4 flex items-center gap-2">
                    <div style="background: var(--color-primary-light); color: var(--color-primary); padding: 8px; border-radius: 50%;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-secondary);">
                            <?= htmlspecialchars($utente["username"]) ?>
                        </div>
                        <div style="font-size: var(--font-size-sm); color: var(--color-text-muted);">
                            <?= htmlspecialchars($utente["email"]) ?>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <!-- ORDINI DELL'UTENTE -->
                    <?php foreach ($utente["ordini"] as $ordine): ?>
                        <div style="background-color: var(--color-bg); border-radius: var(--radius-md); padding: var(--space-4); border: 1px solid var(--color-border);">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div style="font-size: var(--font-size-lg); font-weight: 600; color: var(--color-secondary); margin-bottom: var(--space-2);">
                                        Ordine #<?= htmlspecialchars($ordine["id_ordine"]) ?>
                                    </div>
                                    <div style="font-size: var(--font-size-sm); color: var(--color-text-muted);">
                                        Ordinato il: <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["data_ordine"]) ?></strong><br>
                                        Ritiro previsto: <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["data_ritiro"]) ?></strong>                       
                                    </div>
                                </div>
                                <div class="badge <?php 
                                    if(trim($ordine['stato']) == 'Pronto') echo 'badge-success'; 
                                    else if(trim($ordine['stato']) == 'In preparazione') echo 'badge-warning';
                                    else echo 'badge-primary'; 
                                ?>">
                                    <?= htmlspecialchars($ordine["stato"]) ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <strong style="font-size: var(--font-size-sm); color: var(--color-text);">Prodotti:</strong>
                                <ul style="margin-top: var(--space-1); margin-left: var(--space-4); list-style-type: disc; color: var(--color-text-muted); font-size: var(--font-size-sm);">
                                    <?php foreach ($ordine["prodotti"] as $p): ?>
                                        <li><?= htmlspecialchars($p["nome"]) ?> &times; <?= htmlspecialchars($p["quantita"]) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <a class="btn btn-primary btn-sm" href="update.php?id=<?= htmlspecialchars($ordine["id_ordine"]) ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: -4px;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                Gestisci Ordine
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
    
    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>

</body>
</html>dy>
</html>


