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

$db = new Database("localhost", "my_saqlain", "root", "");

// Action handler for quick status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_action'])) {
    $oid = intval($_POST['id_ordine']);
    $nuovo_stato = $_POST['nuovo_stato'];
    $db->changeStatus($oid, $nuovo_stato);
    header("Location: manage.php");
    exit();
}

$righe = $db->getAllOrdiniAttivi();

$ordini = [];
foreach ($righe as $r) {
    $oid = $r["id_ordine"];
    if (!isset($ordini[$oid])) {
        $ordini[$oid] = [
            "id_ordine"   => $oid,
            "utente"      => $r["username"],
            "data_ordine" => $r["data_ordine"],
            "data_ritiro" => $r["data_ritiro"],
            "stato"       => $r["stato"],
            "metodo"      => $r["metodo"] ?? 'Contanti',
            "nota"        => $r["nota"],
            "prodotti"    => []
        ];
    }
    $ordini[$oid]["prodotti"][] = [
        "nome"     => $r["nome"],
        "quantita" => $r["quantita"]
    ];
}

$in_attesa = [];
$in_preparazione = [];
$pronti = [];

foreach ($ordini as $o) {
    $st = strtolower(trim($o['stato']));
    if ($st === 'in attesa') {
        $in_attesa[] = $o;
    } elseif ($st === 'in preparazione') {
        $in_preparazione[] = $o;
    } elseif ($st === 'pronto') {
        $pronti[] = $o;
    } else {
        $in_attesa[] = $o;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Operativa - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 992px) {
            .kanban-board {
                grid-template-columns: 1fr;
            }
        }
        .kanban-col {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            min-height: 70vh;
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-sm);
        }
        .kanban-header {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 3px solid;
            color: var(--color-secondary);
        }
        .header-attesa { border-color: #f59e0b; }
        .header-prep { border-color: #3b82f6; }
        .header-pronto { border-color: #10b981; }
        
        .order-card {
            background: white;
            border-radius: var(--radius-md);
            padding: var(--space-4);
            margin-bottom: var(--space-4);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
            border-left: 4px solid var(--color-border);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .card-attesa { border-left-color: #f59e0b; }
        .card-prep { border-left-color: #3b82f6; }
        .card-pronto { border-left-color: #10b981; }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--color-secondary);
            font-size: 18px;
        }
        .order-card-user {
            font-size: 14px;
            color: var(--color-text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .order-items-list {
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 14px;
            margin-bottom: 12px;
            background: var(--color-background);
            padding: 10px;
            border-radius: 6px;
        }
        .order-items-list li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .order-items-list li:last-child {
            margin-bottom: 0;
        }
        .qty-badge {
            color: var(--color-primary);
            font-weight: 700;
            margin-right: 6px;
            display: inline-block;
            min-width: 20px;
        }
        .order-note {
            font-size: 13px;
            color: #b91c1c;
            background: #fef2f2;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 12px;
            border-left: 3px solid #ef4444;
        }
        .time-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #f3f4f6;
            color: #374151;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .time-badge.urgent {
            background: #fef2f2;
            color: #ef4444;
        }
        .kanban-footer {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
        .kanban-footer form {
            flex: 1;
        }
        .kanban-footer button {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }
        .btn-ico {
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
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
                <li><a class="nav-item" href="../ordini/my_ordini.php">I Miei Ordini</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item active" href="manage.php">Gestione Ordini</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a class="nav-item" href="../amministrazione/statistiche.php">Statistiche</a></li>
                <li>
                    <a class="nav-icon-btn" href="../auth/profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 style="font-size: var(--font-size-3xl); margin-bottom: 4px;">Gestione Operativa</h2>
                <p style="color: var(--color-text-muted);">Sposta gli ordini tra le colonne per aggiornarne lo stato.</p>
            </div>
            <a href="storico_ordini.php" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Storico
            </a>
        </div>

        <div class="kanban-board">
            
            <!-- COLONNA IN ATTESA -->
            <div class="kanban-col animate-fade-in">
                <div class="kanban-header header-attesa">
                    <span>In Attesa</span>
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #b45309;"><?= count($in_attesa) ?></span>
                </div>
                
                <?php foreach($in_attesa as $o): 
                    $is_urgent = strtotime($o['data_ritiro']) < strtotime('+10 minutes');
                ?>
                <div class="order-card card-attesa">
                    <div class="order-card-header">
                        <span>#<?= str_pad($o['id_ordine'], 4, '0', STR_PAD_LEFT) ?></span>
                        <span class="time-badge <?= $is_urgent ? 'urgent' : '' ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <?= date('H:i', strtotime($o['data_ritiro'])) ?>
                        </span>
                    </div>
                    
                    <div class="order-card-user">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <strong><?= htmlspecialchars($o['utente']) ?></strong>
                        <span style="font-size: 12px; margin-left: auto;"><?= htmlspecialchars($o['metodo']) ?></span>
                    </div>
                    
                    <?php if(!empty($o['nota'])): ?>
                    <div class="order-note">📝 <?= htmlspecialchars($o['nota']) ?></div>
                    <?php endif; ?>
                    
                    <ul class="order-items-list">
                        <?php foreach($o['prodotti'] as $p): ?>
                        <li>
                            <span><span class="qty-badge"><?= $p['quantita'] ?>x</span> <?= htmlspecialchars($p['nome']) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="kanban-footer">
                        <a href="update.php?id=<?= $o['id_ordine'] ?>" class="btn btn-secondary btn-sm btn-ico" title="Modifica Completa">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </a>
                        <form method="POST">
                            <input type="hidden" name="quick_action" value="1">
                            <input type="hidden" name="id_ordine" value="<?= $o['id_ordine'] ?>">
                            <input type="hidden" name="nuovo_stato" value="In preparazione">
                            <button class="btn btn-primary btn-sm" style="background: #3b82f6; border-color: #3b82f6;">
                                Prepara <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:4px;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- COLONNA IN PREPARAZIONE -->
            <div class="kanban-col animate-fade-in" style="animation-delay: 0.1s;">
                <div class="kanban-header header-prep">
                    <span>In Preparazione</span>
                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #1d4ed8;"><?= count($in_preparazione) ?></span>
                </div>
                
                <?php foreach($in_preparazione as $o): 
                    $is_urgent = strtotime($o['data_ritiro']) < strtotime('+5 minutes');
                ?>
                <div class="order-card card-prep">
                    <div class="order-card-header">
                        <span>#<?= str_pad($o['id_ordine'], 4, '0', STR_PAD_LEFT) ?></span>
                        <span class="time-badge <?= $is_urgent ? 'urgent' : '' ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <?= date('H:i', strtotime($o['data_ritiro'])) ?>
                        </span>
                    </div>
                    
                    <div class="order-card-user">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <strong><?= htmlspecialchars($o['utente']) ?></strong>
                    </div>
                    
                    <ul class="order-items-list">
                        <?php foreach($o['prodotti'] as $p): ?>
                        <li>
                            <span><span class="qty-badge"><?= $p['quantita'] ?>x</span> <?= htmlspecialchars($p['nome']) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="kanban-footer">
                        <form method="POST" style="flex: 0 0 auto;">
                            <input type="hidden" name="quick_action" value="1">
                            <input type="hidden" name="id_ordine" value="<?= $o['id_ordine'] ?>">
                            <input type="hidden" name="nuovo_stato" value="In attesa">
                            <button class="btn btn-secondary btn-sm btn-ico" title="Riporta in attesa">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="quick_action" value="1">
                            <input type="hidden" name="id_ordine" value="<?= $o['id_ordine'] ?>">
                            <input type="hidden" name="nuovo_stato" value="Pronto">
                            <button class="btn btn-success btn-sm" style="background: #10b981; border-color: #10b981; width: 100%;">
                                Segna Pronto <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- COLONNA PRONTI -->
            <div class="kanban-col animate-fade-in" style="animation-delay: 0.2s;">
                <div class="kanban-header header-pronto">
                    <span>Pronto per Ritiro</span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #047857;"><?= count($pronti) ?></span>
                </div>
                
                <?php foreach($pronti as $o): ?>
                <div class="order-card card-pronto" style="opacity: 0.9;">
                    <div class="order-card-header">
                        <span>#<?= str_pad($o['id_ordine'], 4, '0', STR_PAD_LEFT) ?></span>
                        <span class="time-badge">Ritiro alle <?= date('H:i', strtotime($o['data_ritiro'])) ?></span>
                    </div>
                    
                    <div class="order-card-user">
                        <strong><?= htmlspecialchars($o['utente']) ?></strong>
                    </div>
                    
                    <div class="kanban-footer" style="margin-top: 12px;">
                        <form method="POST" style="flex: 0 0 auto;">
                            <input type="hidden" name="quick_action" value="1">
                            <input type="hidden" name="id_ordine" value="<?= $o['id_ordine'] ?>">
                            <input type="hidden" name="nuovo_stato" value="In preparazione">
                            <button class="btn btn-secondary btn-sm btn-ico" title="Riporta in preparazione">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="quick_action" value="1">
                            <input type="hidden" name="id_ordine" value="<?= $o['id_ordine'] ?>">
                            <input type="hidden" name="nuovo_stato" value="Completato">
                            <button class="btn btn-primary btn-sm" style="background: var(--color-secondary); border-color: var(--color-secondary); width: 100%;">
                                Consegna <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:4px;"><path d="M5 12l5 5L20 7"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>
    
    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>

</body>
</html>
