<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id_ordine'])) {
    header("Location: ../../index.php");
    exit;
}

$id_ordine = (int)$_GET['id_ordine'];
$id_utente = $_SESSION['user_id'];

$host="localhost";
$user="root";
$pass="";
$db="my_saqlain";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Errore connessione");
}

// Get order details securely
$stmt = $conn->prepare("SELECT o.data_ordine, o.stato, o.metodo, o.nota, o.data_ritiro FROM SB_ordine o WHERE o.id_ordine = ? AND o.id_utente = ?");
$stmt->bind_param("ii", $id_ordine, $id_utente);
$stmt->execute();
$res_ordine = $stmt->get_result();

if ($res_ordine->num_rows === 0) {
    die("Ordine non trovato o non autorizzato.");
}
$ordine = $res_ordine->fetch_assoc();
$stmt->close();

// Get order items
$stmt_items = $conn->prepare("
    SELECT p.nome, p.prezzo, d.quantita 
    FROM SB_dettaglio_ordine d 
    JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto 
    WHERE d.id_ordine = ?
");
$stmt_items->bind_param("i", $id_ordine);
$stmt_items->execute();
$res_items = $stmt_items->get_result();

$items = [];
$totale = 0;
while ($row = $res_items->fetch_assoc()) {
    $items[] = $row;
    $totale += $row['prezzo'] * $row['quantita'];
}
$stmt_items->close();

function bgStato($stato) {
    switch(strtolower($stato)) {
        case 'in attesa': return 'rgba(245, 158, 11, 0.1)';
        case 'pronto': return 'rgba(16, 185, 129, 0.1)';
        case 'ritirato': return 'rgba(59, 130, 246, 0.1)';
        case 'cancellato': return 'rgba(239, 68, 68, 0.1)';
        default: return '#f3f4f6';
    }
}
function colStato($stato) {
    switch(strtolower($stato)) {
        case 'in attesa': return '#f59e0b';
        case 'pronto': return '#10b981';
        case 'ritirato': return '#3b82f6';
        case 'cancellato': return '#ef4444';
        default: return '#6b7280';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Ordine #<?= htmlspecialchars($id_ordine) ?> - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <style>
        .conf-container { max-width: 600px; margin: 40px auto; padding: 20px; }
        .conf-card { background: white; border-radius: 16px; padding: 30px; box-shadow: var(--shadow-md); text-align: center; }
        .success-icon { 
            width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); color: #10b981;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .conf-header h1 { font-size: 24px; font-weight: 700; color: var(--color-text); margin-bottom: 8px; }
        .conf-header p { color: var(--color-text-muted); font-size: 16px; }
        
        .order-summary { margin-top: 30px; text-align: left; }
        .summary-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--color-border); }
        .summary-item:last-child { border-bottom: none; }
        .summary-total { display: flex; justify-content: space-between; padding: 16px 0; border-top: 2px solid var(--color-border); font-weight: 700; font-size: 18px; color: var(--color-text); margin-top: 10px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; text-align: left; }
        .info-box { background: var(--color-background); padding: 15px; border-radius: 12px; }
        .info-label { font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-value { font-size: 16px; font-weight: 500; color: var(--color-text); }
        
        .badge { display: inline-block; padding: 6px 12px; border-radius: 99px; font-size: 14px; font-weight: 600; }
        
        .action-btns { margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
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
                <li><a class="nav-item" href="index_order.php">Ordina</a></li>
                <li><a class="nav-item" href="../ordini/my_ordini.php">I Miei Ordini</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item" href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                    <li><a class="nav-item" href="../amministrazione/statistiche.php">Statistiche</a></li>
                <?php endif; ?>
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

    <main class="main-content">
        <div class="conf-container">
            <div class="conf-card animate-fade-in">
                
                <div class="conf-header">
                    <div class="success-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h1>Ordine Ricevuto!</h1>
                    <p>Grazie per il tuo ordine. Lo stiamo preparando.</p>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">Numero Ordine</div>
                        <div class="info-value">#<?= str_pad($id_ordine, 5, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Stato</div>
                        <div class="info-value">
                            <span class="badge" style="background: <?= bgStato($ordine['stato']) ?>; color: <?= colStato($ordine['stato']) ?>">
                                <?= htmlspecialchars($ordine['stato']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Ritiro Stimato</div>
                        <div class="info-value">
                            <?= date('H:i', strtotime($ordine['data_ritiro'])) ?>
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Pagamento</div>
                        <div class="info-value"><?= htmlspecialchars($ordine['metodo']) ?></div>
                    </div>
                </div>

                <div class="order-summary">
                    <div style="font-weight: 600; margin-bottom: 12px; font-size: 18px;">Riepilogo Ordine</div>
                    
                    <?php foreach($items as $item): ?>
                    <div class="summary-item">
                        <div>
                            <span style="font-weight: 500; color: var(--color-primary); margin-right: 8px;"><?= $item['quantita'] ?>x</span>
                            <span><?= htmlspecialchars($item['nome']) ?></span>
                        </div>
                        <div style="font-weight: 500;">€<?= number_format($item['prezzo'] * $item['quantita'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-total">
                        <span>Totale</span>
                        <span>€<?= number_format($totale, 2) ?></span>
                    </div>
                </div>

                <div class="action-btns">
                    <a href="../../index.php" class="btn btn-secondary">Torna alla Home</a>
                    <a href="../ordini/my_ordini.php" class="btn btn-primary">I Miei Ordini</a>
                </div>

            </div>
        </div>
    </main>

    <footer class="global-footer mt-auto">
        <p>Progetto Speedy Break - 5CIN &copy; 2026</p>
    </footer>

</body>
</html>
