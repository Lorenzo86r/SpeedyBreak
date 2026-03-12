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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Storico Ordini</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
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
                    <li><a class="nav-item" href="manage.php">Gestione Ordini</a></li>
                    <li><a class="nav-item active" href="storico_ordini.php">Storico</a></li>
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
            <h2 style="font-size: var(--font-size-3xl);">Storico Ordini (Completati)</h2>
            <a href="manage.php" class="btn btn-secondary">← Torna agli ordini attivi</a>
        </div>

        <!-- CARD PER OGNI DATA -->
        <?php foreach ($storico as $data => $utenti): ?>
            <div class="card mb-6 animate-fade-in">
                <!-- INTESTAZIONE DATA -->
                <div class="mb-4" style="font-size: var(--font-size-2xl); font-weight: 700; color: var(--color-secondary);">
                    📅 <?= htmlspecialchars($data) ?>
                </div>

                <div class="flex flex-col gap-6">
                    <!-- UTENTI DI QUELLA DATA -->
                    <?php foreach ($utenti as $utente): ?>
                        <div style="background-color: var(--color-bg); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                            <div class="mb-4 flex items-center gap-2">
                                <div style="font-size: var(--font-size-xl); font-weight: 600; color: var(--color-secondary);">
                                    👤 <?= htmlspecialchars($utente["username"]) ?>
                                </div>
                                <span style="font-size: var(--font-size-sm); color: var(--color-text-muted);">
                                    (<?= htmlspecialchars($utente["email"]) ?>)
                                </span>
                            </div>

                            <div class="flex flex-col gap-4">
                                <!-- ORDINI DELL'UTENTE -->
                                <?php foreach ($utente["ordini"] as $ordine): ?>
                                    <div style="background-color: var(--color-surface); border-radius: var(--radius-sm); padding: var(--space-3); border: 1px solid var(--color-border);">
                                        <div style="font-size: var(--font-size-lg); font-weight: 600; color: var(--color-secondary); margin-bottom: var(--space-2);">
                                            Ordine #<?= htmlspecialchars($ordine["id_ordine"]) ?>
                                        </div>

                                        <div style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: var(--space-2);">
                                            Ritiro: <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["data_ritiro"]) ?></strong><br>
                                            Metodo: <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["metodo"]) ?></strong><br>
                                            Nota: <strong style="color: var(--color-text);"><?= htmlspecialchars($ordine["nota"]) ?></strong>
                                        </div>

                                        <div>
                                            <strong style="font-size: var(--font-size-sm); color: var(--color-text);">Prodotti:</strong><br>
                                            <ul style="margin-top: var(--space-1); margin-left: var(--space-4); list-style-type: disc; color: var(--color-text-muted); font-size: var(--font-size-sm);">
                                                <?php foreach ($ordine["prodotti"] as $p): ?>
                                                    <li><?= htmlspecialchars($p["nome"]) ?> &times; <?= htmlspecialchars($p["quantita"]) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
</html>
